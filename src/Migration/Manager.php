<?php

namespace MicroShard\Core\Migration;

use MicroShard\Core\Container;
use MicroShard\Core\Services\DatabaseAdapter;

class Manager
{
    const MIGRATIONS_DIR = 'migration';

    /**
     * @var DatabaseAdapter
     */
    private $adapter;
    /**
     * @var string
     */
    private $migrationDir;
    /**
     * @var Container
     */
    private $container;

    /**
     * @var \Closure
     */
    private $logCallback;

    /**
     * @var MigrationsModel
     */
    private $model;

    /**
     * Manager constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->adapter = $container->getService('db');
        $this->model = new MigrationsModel($this->adapter);
        $this->migrationDir = $container->getConfiguration()->get('base_dir') . self::MIGRATIONS_DIR;
        $this->container = $container;
    }

    /**
     * @param \Closure $logCallback
     * @return Manager
     */
    public function setLogCallback(\Closure $logCallback): Manager
    {
        $this->logCallback = $logCallback;
        return $this;
    }

    /**
     * @param string $message
     * @return Manager
     */
    protected function log(string $message): Manager
    {
        if ($callback = $this->logCallback) {
            $callback($message);
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function migrate()
    {
        $files = $this->getMigrationsFiles();
        $history = $this->getHistory();

        $newFiles = [];
        foreach ($files as $file){
            if (!$this->model->getFieldDescription('file_name')->validateValue($file)) {
                $this->log("invalid file name: $file");
                return $this;
            }

            if (!isset($history[$file])) {
                $newFiles[] = $file;
            }
        }

        if (count($newFiles) > 0) {
            foreach ($newFiles as $file){
                try {
                    $this->applyMigrationFile($file);
                } catch (\Exception $e) {
                    $this->log($e->getMessage());
                    $this->log("migration aborted");
                    break;
                }
            }
        } else {
            $this->log("no new migrations found.");
        }

        return $this;
    }

    /**
     * @return array
     */
    protected function getMigrationsFiles()
    {
        $files = scandir($this->migrationDir);
        $filter = [];
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $filter[] = $file;
        }
        return $filter;
    }

    /**
     * @return array
     */
    protected function getHistory()
    {
        return $this->model->list([], 'file_name');
    }

    /**
     * @param string $fileName
     * @return $this
     */
    protected function applyMigrationFile(string $fileName)
    {
        $fullPath = $this->migrationDir . '/' . $fileName;
        if (file_exists($fullPath)) {
            $this->log("starting migration: $fileName");
            $migration = require_once $fullPath;
            if ($migration){
                $migration($this->container);
                $this->addHistory($fileName);
            }
            $this->log("finished migration: $fileName");
        }
        return $this;
    }

    /**
     * @param string $fileName
     * @return $this
     */
    protected function addHistory(string $fileName)
    {
        $this->model->create([
            'file_name' => $fileName,
            'executed_at' => $this->model->getCurrentDateTime()
        ]);
        return $this;
    }

    /**
     * @return int
     */
    public function tableExists()
    {
        $table = $this->model->getTable();
        return $this->adapter->exec("SHOW TABLES LIKE '$table'")->count();
    }

    /**
     * @return $this
     */
    public function createTable()
    {
        $table = $this->model->getTable();
        $this->adapter->exec("
              CREATE TABLE $table (
                  id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                  file_name VARCHAR(255) NOT NULL,
                  executed_at DATETIME NOT NULL,
                  PRIMARY KEY (id),
                  UNIQUE `MIGRATION_FILENAME_UNQ` (file_name)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
        return $this;
    }
}

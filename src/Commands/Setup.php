<?php

namespace MicroShard\Core\Commands;

use MicroShard\Core\Console\Command;
use MicroShard\Core\Migration\Manager;
use MicroShard\Core\Services\DatabaseAdapter;

class Setup extends Command
{
    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Setup Database.';
    }

    protected function execute()
    {
        $dbUser = $this->getContainer()->getConfiguration()->get('db_user');
        $dbHost = $this->getContainer()->getConfiguration()->get('db_host');
        $dbName = $this->getContainer()->getConfiguration()->get('db_name');

        //create temporary adapter without defined database
        $adapter = new DatabaseAdapter(
            $dbHost,
            $dbUser,
            $this->getContainer()->getConfiguration()->get('db_pass')
        );

        $exists = $adapter->exec("SHOW DATABASES LIKE '$dbName'")->count();
        if ($exists == 0) {
            $adapter->exec("
                CREATE DATABASE $dbName CHARACTER SET utf8 COLLATE utf8_general_ci;
                GRANT ALL ON $dbName.* TO '$dbUser'@'%';
                FLUSH PRIVILEGES;    
            ");
            $this->echoLine("Database $dbName created.");
        } else {
            $this->echoLine("Database $dbName already exists.");
        }

        // switch to default adapter after Database exists
        $manager = new Manager($this->getContainer());
        if ($manager->tableExists() == 0) {
            $manager->createTable();
            $this->echoLine("Migrations Table created.");
        } else {
            $this->echoLine("Migrations Tbale already exists.");
        }
    }
}
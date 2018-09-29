<?php

namespace MicroShard\Core;

use GuzzleHttp\Psr7\ServerRequest;
use MicroShard\JsonRpcServer\Directory;
use MicroShard\JsonRpcServer\Server;
use MicroShard\Core\Exception\ServiceException;
use MicroShard\JsonRpcServer\Security\AuthenticatorInterface;

abstract class Core
{
    const ERROR_CODE_UNKNOWN = 1000;

    /**
     * @var Container
     */
    protected $container;

    /**
     * Core constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * @return Configuration
     */
    public function getConfiguration(): Configuration
    {
        return $this->getContainer()->getConfiguration();
    }

    /**
     * @return Directory
     */
    public abstract function getDirectory(): Directory;

    /**
     * @return AuthenticatorInterface
     */
    public abstract function getAuthenticator(): AuthenticatorInterface;

    public function runWeb()
    {
        set_error_handler(function($errno, $errstr, $errfile, $errline, $context) {
            Server::getResponse(200, [
                'status' => 500,
                'error' => Core::ERROR_CODE_UNKNOWN,
                'message' => $errstr,
                'payload' => []
            ])->send();
        });

        try {
            $request = ServerRequest::fromGlobals();
            $server = new Server($this->getDirectory(), $this->getAuthenticator());
            $server->run($request);
        } catch (ServiceException $exception) {
            Server::getResponse(200, [
                'status' => $exception->getStatusCode(),
                'error' => $exception->getCode(),
                'message' => $exception->getMessage(),
                'payload' => []
            ])->send();
        } catch (\Exception $exception) {
            Server::getResponse(200, [
                'status' => 500,
                'error' => Core::ERROR_CODE_UNKNOWN,
                'message' => $exception->getMessage(),
                'payload' => []
            ])->send();
        }
    }

    /**
     * @return Console
     */
    protected function getConsole()
    {
        $console = new Console($this->container);
        $console->registerCommand('database:setup', '\MicroShard\Core\Commands\Setup');
        $console->registerCommand('database:migrate', '\MicroShard\Core\Commands\Migrate');
        return $console;
    }

    public function runCli(array $args)
    {
        try {
            $this->getConsole()->run($args);
        } catch (\Exception $e) {
            echo $e->getMessage() . PHP_EOL;
            echo $e->getTraceAsString() . PHP_EOL;
        }
    }
}
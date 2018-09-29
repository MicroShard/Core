<?php

namespace MicroShard\Core;

use Closure;
use MicroShard\JsonRpcServer\Exception\RpcException;
use MicroShard\Core\Exception\ServiceException;

class Container
{

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var Closure[]
     */
    protected $serviceDefinitions = [];

    /**
     * @var array
     */
    protected $services = [];

    /**
     * Container constructor.
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return Configuration
     */
    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    /**
     * @param $name
     * @param Closure $constructor
     * @return $this
     */
    public function addServiceDefinition($name, Closure $constructor)
    {
        $this->serviceDefinitions[$name] = $constructor;
        return $this;
    }

    /**
     * @param $name
     * @param $service
     * @return $this
     */
    public function addService($name, $service)
    {
        $this->services[$name] = $service;
        return $this;
    }

    /**
     * @param $name
     * @return mixed
     * @throws RpcException
     */
    public function getService($name)
    {
        if (!isset($this->services[$name])) {
            if (!isset($this->serviceDefinitions[$name])) {
                throw ServiceException::create("unknown service: $name", 350, 500);
            }
            $this->addService($name, $this->serviceDefinitions[$name]($this));
        }
        return $this->services[$name];
    }
}
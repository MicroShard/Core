<?php

namespace MicroShard\Core\JsonRpc;

use MicroShard\JsonRpcServer\Directory;
use MicroShard\JsonRpcServer\HandlerInterface;
use MicroShard\Core\Container;

class ServiceDirectory extends Directory
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * ServiceDirectory constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @return Container
     */
    protected function getContainer()
    {
        return $this->container;
    }

    /**
     * @param string $resource
     * @param string|array $method
     * @param int $version
     * @param string $serviceName
     * @return $this
     */
    public function addService($resource, $method, $version, $serviceName)
    {
        if (is_array($method)) {
            foreach ($method as $meth) {
                $this->initDefinition($resource, $meth, $version);
                $this->definitions[$resource][$meth][$version]['service'] = $serviceName;
            }
        } else {
            $this->initDefinition($resource, $method, $version);
            $this->definitions[$resource][$method][$version]['service'] = $serviceName;
        }
        return $this;
    }

    /**
     * @param array $definition
     * @param string $resource
     * @param string $method
     * @param string $version
     * @return mixed|null
     */
    public function getHandlerExtended(array $definition, $resource, $method, $version)
    {
        $service = null;
        if (isset($definition['service'])) {
            $service = $this->getContainer()->getService($definition['service']);
        }
        return $service;
    }
}
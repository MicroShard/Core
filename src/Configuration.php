<?php

namespace MicroShard\Core;

class Configuration
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * Configuration constructor.
     * @param array $envVarNames
     */
    public function __construct(array $envVarNames = [])
    {
        foreach ($envVarNames as $alias => $varName){
            $this->data[$alias] = getenv($varName);
        }
    }

    /**
     * @param string $varName
     * @param mixed $value
     * @return $this
     */
    public function set($varName, $value)
    {
        $this->data[$varName] = $value;
        return $this;
    }

    /**
     * @param string $varName
     * @return mixed
     */
    public function get($varName)
    {
        return (isset($this->data[$varName])) ? $this->data[$varName] : null;
    }

    /**
     * @param string $varName
     * @return bool
     */
    public function has($varName)
    {
        return (isset($this->data[$varName]) && !is_null($this->data[$varName]));
    }
}
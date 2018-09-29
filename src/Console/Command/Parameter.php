<?php

namespace MicroShard\Core\Console\Command;

class Parameter
{

    /**
     * @var string
     */
    private $long;

    /**
     * @var bool
     */
    private $optional;

    /**
     * @var string
     */
    private $short;

    /**
     * @var string
     */
    private $description;

    /**
     * @var \Closure
     */
    private $validator;

    /**
     * @var mixed
     */
    private $value;

    /**
     * Parameter constructor.
     * @param string $long
     * @param bool $optional
     */
    public function __construct(string $long, bool $optional)
    {
        $this->long = $long;
        $this->optional = $optional;
    }

    /**
     * @param string $short
     * @return Parameter
     */
    public function setShort(string $short): Parameter
    {
        $this->short = $short;
        return $this;
    }

    /**
     * @param string $description
     * @return Parameter
     */
    public function setDescription(string $description): Parameter
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @param \Closure $validator
     * @return Parameter
     */
    public function setValidator(\Closure $validator): Parameter
    {
        $this->validator = $validator;
        return $this;
    }

    /**
     * @return string
     */
    public function getLong(): string
    {
        return $this->long;
    }

    /**
     * @return boolean
     */
    public function isOptional(): bool
    {
        return $this->optional;
    }

    /**
     * @return string
     */
    public function getShort(): string
    {
        return $this->short;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $value
     * @return bool
     */
    public function validate(string $value): bool
    {
        if ($validator = $this->validator){
            return $validator($value);
        }
        return true;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     * @return Parameter
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }
}
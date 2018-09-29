<?php

namespace MicroShard\Core\Services\DatabaseAdapter;

use MicroShard\Core\Data\Model\FieldDescription;

class QueryValidator
{
    const LIST_AND = 'and';
    const LIST_OR = 'or';

    const ELEMENT_FIELD = 'field';
    const ELEMENT_OPERATOR = 'operator';
    const ELEMENT_VALUE = 'value';
    const ELEMENT_REFERENCE = 'reference';

    const OPERATOR_EQUALS = 'eq';
    const OPERATOR_GREATER = 'gt';
    const OPERATOR_GREATER_EQUALS = 'gte';
    const OPERATOR_LESSER = 'lt';
    const OPERATOR_LESSER_EQUALS = 'lte';
    const OPERATOR_NOT_EQUAL = 'neq';
    const OPERATOR_CONTAINS = 'cv';
    const OPERATOR_NOT_CONTAINS = 'ncv';
    const OPERATOR_EMPTY = 'ey';
    const OPERATOR_NOT_EMPTY = 'ney';
    const OPERATOR_IN = 'in';
    const OPERATOR_NOT_IN = 'nin';

    /**
     * @var array
     */
    protected $operators = [
        self::OPERATOR_CONTAINS,
        self::OPERATOR_EMPTY,
        self::OPERATOR_EQUALS,
        self::OPERATOR_GREATER,
        self::OPERATOR_GREATER_EQUALS,
        self::OPERATOR_LESSER,
        self::OPERATOR_LESSER_EQUALS,
        self::OPERATOR_NOT_CONTAINS,
        self::OPERATOR_NOT_EMPTY,
        self::OPERATOR_NOT_EQUAL,
        self::OPERATOR_IN,
        self::OPERATOR_NOT_IN
    ];

    /**
     * @var array
     */
    protected $unaryOperators = [
        self::OPERATOR_EMPTY,
        self::OPERATOR_NOT_EMPTY
    ];

    /**
     * @var FieldDescription[]
     */
    protected $description;

    /**
     * QueryResolver constructor.
     * @param FieldDescription[] $description
     */
    public function __construct(array $description)
    {
        $this->description = $description;
    }

    /**
     * @param array $query
     * @return $this
     * @throws \MicroShard\JsonRpcServer\Exception\RpcException
     */
    public function validate(array $query)
    {
        if (!$this->isList($query)) {
            throw QueryValidatorException::create('invalid query', 370);
        }
        $this->validateList($query);
        return $this;
    }

    /**
     * @param array $list
     * @return $this
     * @throws \MicroShard\JsonRpcServer\Exception\RpcException
     */
    protected function validateList(array $list)
    {
        $type = (isset($list[self::LIST_AND])) ? self::LIST_AND : self::LIST_OR;
        if (!is_array($list[$type])) {
            throw QueryValidatorException::create('invalid query', 371);
        }

        foreach ($list[$type] as $item) {
            if (!is_array($item)) {
                throw QueryValidatorException::create('invalid query', 372);
            }

            if ($this->isList($item)) {
                $this->validateList($item);
            } else if($this->isElement($item)) {
                $this->validateElement($item);
            } else {
                throw QueryValidatorException::create('invalid query', 373);
            }
        }
        return $this;
    }

    /**
     * @param array $data
     * @return $this
     * @throws \MicroShard\JsonRpcServer\Exception\RpcException
     */
    protected function validateElement(array $data)
    {
        if (!isset($data[self::ELEMENT_FIELD]) || !isset($data[self::ELEMENT_OPERATOR])){
            throw QueryValidatorException::create('invalid query - missing field or operator', 374);
        }
        $field = $data[self::ELEMENT_FIELD];
        $operator = $data[self::ELEMENT_OPERATOR];

        if (!isset($this->description[$field])) {
            throw QueryValidatorException::create('invalid query - field $field does not exists', 375);
        }

        if (!in_array($operator, $this->operators)) {
            throw QueryValidatorException::create("invalid query - unknown operator $operator", 376);
        }

        if (!in_array($operator, $this->unaryOperators)) {

            if (!isset($data[self::ELEMENT_VALUE]) && !isset($data[self::ELEMENT_REFERENCE])){
                throw QueryValidatorException::create("invalid query - missing value or reference", 377);
            }
            if (isset($data[self::ELEMENT_VALUE])) {
                $value = $data[self::ELEMENT_VALUE];
                $description = $this->description[$field];
                if (in_array($operator,[self::OPERATOR_IN, self::OPERATOR_NOT_IN]) && is_array($value)) {
                    foreach ($value as $val) {
                        if (!$description->validateValue($val)){
                            throw QueryValidatorException::create("invalid query - list contains invalid value $val", 380);
                        }
                    }
                } else {
                    if (!$description->validateValue($value)){
                        throw QueryValidatorException::create("invalid query - invalid value $value", 378);
                    }
                }
            } else {
                $reference = $data[self::ELEMENT_REFERENCE];
                if (!$this->description[$reference]) {
                    throw QueryValidatorException::create("invalid query - reference $reference does not exists", 379);
                }
            }
        }

        return $this;
    }

    /**
     * @param array $data
     * @return bool
     */
    public function isList(array $data)
    {
        return isset($data[self::LIST_AND]) || isset($data[self::LIST_OR]);
    }

    /**
     * @param array $data
     * @return bool
     */
    public function isElement(array $data)
    {
        return isset($data[self::ELEMENT_FIELD]) && isset($data[self::ELEMENT_OPERATOR]);
    }
}

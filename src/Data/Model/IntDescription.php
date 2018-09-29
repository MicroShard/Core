<?php

namespace MicroShard\Core\Data\Model;

use MicroShard\Core\Services\DatabaseAdapter\QueryValidator;

class IntDescription extends FieldDescription
{
    const TYPE = 'Int';

    /**
     * @var bool
     */
    private $unsigned = false;

    /**
     * @return $this
     */
    public function setUnsigned()
    {
        $this->unsigned = true;
        return $this;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function validateValue(&$value): bool
    {
        $valid = is_numeric($value);
        $valid &= ($this->unsigned) ? $value >= 0 : true;

        return ($valid) ? parent::validateValue($value) : $valid;
    }

    /**
     * @return array
     */
    public function getSupportedOperators()
    {
        return [
            QueryValidator::OPERATOR_EQUALS,
            QueryValidator::OPERATOR_NOT_EQUAL,
            QueryValidator::OPERATOR_GREATER,
            QueryValidator::OPERATOR_GREATER_EQUALS,
            QueryValidator::OPERATOR_LESSER,
            QueryValidator::OPERATOR_LESSER_EQUALS,
            QueryValidator::OPERATOR_EMPTY,
            QueryValidator::OPERATOR_NOT_EMPTY,
            QueryValidator::OPERATOR_IN,
            QueryValidator::OPERATOR_NOT_IN
        ];
    }
}

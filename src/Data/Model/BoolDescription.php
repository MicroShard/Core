<?php

namespace MicroShard\Core\Data\Model;

use MicroShard\Core\Services\DatabaseAdapter\QueryValidator;

class BoolDescription extends FieldDescription
{
    const TYPE = 'Bool';

    /**
     * @param mixed $value
     * @return bool
     */
    public function validateValue(&$value): bool
    {
        $valid = $value === true || $value === false
            || $value == 1 || $value == 0;

        if ($valid) {
            $value = ($value) ? 1 : 0;
        }

        return $valid;
    }

    /**
     * @return array
     */
    public function getSupportedOperators()
    {
        return [
            QueryValidator::OPERATOR_EQUALS,
            QueryValidator::OPERATOR_NOT_EQUAL,
            QueryValidator::OPERATOR_EMPTY,
            QueryValidator::OPERATOR_NOT_EMPTY
        ];
    }
}

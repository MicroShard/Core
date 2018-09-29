<?php

namespace MicroShard\Core\Data\Model;

use MicroShard\Core\Services\DatabaseAdapter\QueryValidator;

class DateTimeDescription extends FieldDescription
{
    const TYPE = 'DateTime';

    /**
     * @param mixed $value
     * @return bool
     */
    public function validateValue(&$value): bool
    {
        $valid = is_string($value);
        if ($valid && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            $value = $value . ' 00:00:00';
        } else {
            $valid &= preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value);
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
            QueryValidator::OPERATOR_GREATER,
            QueryValidator::OPERATOR_GREATER_EQUALS,
            QueryValidator::OPERATOR_LESSER,
            QueryValidator::OPERATOR_LESSER_EQUALS,
            QueryValidator::OPERATOR_EMPTY,
            QueryValidator::OPERATOR_NOT_EMPTY
        ];
    }
}

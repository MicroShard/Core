<?php

namespace MicroShard\Core\Services\DatabaseAdapter;

use MicroShard\Core\Exception\ServiceException;

class QueryValidatorException extends ServiceException
{
    const DEFAULT_STATUS_CODE = 400;
}
<?php

namespace MicroShard\Core\Migration;

use MicroShard\Core\Data\Model;
use MicroShard\Core\Data\Model\DateTimeDescription;
use MicroShard\Core\Data\Model\IntDescription;
use MicroShard\Core\Data\Model\StringDescription;

class MigrationsModel extends Model
{
    public function init(): Model
    {
        $this->setTable('migrations');
        $this->addDescription((new IntDescription('id'))->setUnsigned()->setPrimaryKey()->setAutoIncrement());
        $this->addDescription((new StringDescription('file_name'))->setMaxLength(255));
        $this->addDescription(new DateTimeDescription('executed_at'));
        return $this;
    }
}

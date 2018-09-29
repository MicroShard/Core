<?php

namespace MicroShard\Core\Data;

use MicroShard\JsonRpcServer\Exception\RpcException;
use MicroShard\Core\Data\Model\FieldDescription;
use MicroShard\Core\Exception\ServiceException;
use MicroShard\Core\Services\DatabaseAdapter;
use MicroShard\Core\Services\DatabaseAdapter\ListBuilder;

abstract class Model
{
    /**
     * @var FieldDescription[]
     */
    protected $description = [];

    /**
     * @var string
     */
    protected $table;

    /**
     * @var string
     */
    protected $autoIncrementField;

    /**
     * @var string
     */
    protected $primaryKey;

    /**
     * @var DatabaseAdapter
     */
    protected $databaseAdapter;

    /**
     * @param DatabaseAdapter $adapter
     */
    public function __construct(DatabaseAdapter $adapter)
    {
        $this->databaseAdapter = $adapter;
        $this->init();
    }

    /**
     * @return FieldDescription[]
     */
    public function getDescriptions(): array
    {
        return $this->description;
    }

    /**
     * @param FieldDescription $description
     * @return $this
     */
    protected function addDescription(FieldDescription $description)
    {
        $this->description[$description->getName()] = $description;
        if ($description->isPrimaryKey()) {
            $this->primaryKey = $description->getName();
        }
        if ($description->isAutoIncrement()) {
            $this->autoIncrementField = $description->getName();
        }

        return $this;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasField(string $name)
    {
        return isset($this->description[$name]);
    }

    /**
     * @param string $name
     * @return FieldDescription
     */
    public function getFieldDescription(string $name)
    {
        return $this->description[$name];
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @param string $table
     * @return Model
     */
    protected function setTable(string $table): Model
    {
        $this->table = $table;
        return $this;
    }

    /**
     * @return string
     */
    protected function getAutoIncrementField()
    {
        return $this->autoIncrementField;
    }

    /**
     * @return string
     */
    protected function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * @return DatabaseAdapter
     */
    protected function getDatabaseAdapter(): DatabaseAdapter
    {
        return $this->databaseAdapter;
    }

    public abstract function init(): Model;

    /**
     * @param array $data
     * @return array
     * @throws RpcException
     */
    public function create(array $data)
    {
        $filtered = [];
        foreach ($this->getDescriptions() as $field => $description){
            if ($description->isReadOnly()) {
                continue;
            }
            $value = (isset($data[$field])) ? $data[$field] : null;
            if (is_null($value) && $this->autoIncrementField == $field) {
                continue;
            }

            if (is_null($value) && !$description->isRequired()) {
                if ($description->getDefaultValue()) {
                    $value = $description->getDefaultValue();
                } else {
                    //skip optional fields with no value
                    continue;
                }
            }

            if (!$description->validateValue($value)) {
                throw ServiceException::create("invalid value for $field", 301, 400);
            }
            $filtered[$field] = $value;
        }
        $filtered = $this->beforeCreate($filtered);
        $filtered = $this->getDatabaseAdapter()->insert(
            $this->getTable(),
            $this->getAutoIncrementField(),
            $this->getDescriptions(),
            $filtered
        );
        $filtered = $this->afterCreate($filtered);
        return $filtered;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function beforeCreate(array $data)
    {
        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function afterCreate(array $data)
    {
        return $data;
    }

    /**
     * @param array $data
     * @return array|bool
     * @throws RpcException
     */
    public function load(array $data)
    {
        $identity = $this->getIdentity($data);
        $result = $this->loadInternal($identity['field'], $identity['value']);

        if (!$result){
            throw ServiceException::create('object not found', 300, 404);
        }
        return $result;
    }

    /**
     * @param string $field
     * @param $value
     * @return array|bool
     */
    protected function loadInternal(string $field, $value)
    {
        return $this->getDatabaseAdapter()
            ->read(
                $this->getTable(),
                $field,
                $value,
                $this->getFieldDescription($field)
            );
    }

    /**
     * @param array $data
     * @return array
     * @throws RpcException
     */
    protected function getIdentity(array $data)
    {
        if (!isset($data['identity'])) {
            throw ServiceException::create('missing object identity', 302, 400);
        }
        $identity = $data['identity'];
        if (!isset($identity['value'])) {
            throw ServiceException::create('missing object identity.value', 303, 400);
        }
        $value = $identity['value'];
        if (!isset($identity['field'])) {
            if (!$this->getPrimaryKey()) {
                throw ServiceException::create('missing object identity.field', 304, 400);
            }
            $field = $this->getPrimaryKey();
        } else {
            $field = $identity['field'];
            if (!$this->hasField($field)){
                throw ServiceException::create('unknown object identity.field', 305, 404);
            }
        }

        if (!$this->getFieldDescription($field)->validateValue($value)){
            throw ServiceException::create("invalid value for object identity.field", 306, 400);
        }

        return [
            'field' => $field,
            'value' => $value
        ];
    }

    /**
     * @param array $payload
     * @return array|bool
     * @throws RpcException
     */
    public function update(array $payload)
    {
        $identity = $this->getIdentity($payload);
        $data = (isset($payload['data']) && is_array($payload['data']))
            ? $payload['data']
            : [];

        $current = $this->loadInternal($identity['field'], $identity['value']);
        if (!$current) {
            throw ServiceException::create('object not found', 307, 404);
        }

        $new = [];
        foreach ($this->getDescriptions() as $field => $description) {
            if ($description->isReadOnly()) {
                continue;
            }
            if (isset($data[$field])) {
                $value = $data[$field];
                if (!$description->validateValue($value)) {
                    throw ServiceException::create("invalid value for $field", 308, 400);
                }
                $new[$field] = $value;
            } else {
                $new[$field] = $current[$field];
            }
        }

        if (count($data)) {
            $new = $this->beforeUpdate($current, $new);
            $new = $this->getDatabaseAdapter()->update(
                $this->getTable(),
                $this->getPrimaryKey(),
                $this->getDescriptions(),
                $new
            );
            $new = $this->afterUpdate($current, $new);
        }
        return $new;
    }

    /**
     * @param array $old
     * @param array $new
     * @return array
     */
    protected function beforeUpdate(array $old, array $new)
    {
        return $new;
    }

    /**
     * @param array $old
     * @param array $new
     * @return array
     */
    protected function afterUpdate(array $old, array $new)
    {
        return $new;
    }

    /**
     * @param array $data
     * @return array
     */
    public function delete(array $data)
    {
        $identity = $this->getIdentity($data);
        $current = $this->loadInternal($identity['field'], $identity['value']);
        if ($current) {
            if ($this->getPrimaryKey()){
                $field = $this->getPrimaryKey();
                $value = $current[$field];
            } else {
                $field = $identity['field'];
                $value = $identity['value'];
            }

            $this->beforeDelete($current);
            $this->getDatabaseAdapter()->delete(
                $this->getTable(),
                $field,
                $value,
                $this->getDescriptions()
            );

            $this->afterDelete($current);
        }

        return [];
    }

    /**
     * @param array $data
     * @return array
     */
    protected function beforeDelete(array $data)
    {
        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function afterDelete(array $data)
    {
        return $data;
    }

    /**
     * @param array $data
     * @param string $assocField
     * @return array
     */
    public function list(array $data, string $assocField = null)
    {
        $result = [];
        $builder = $this->getListBuilder();
        if (isset($data['query'])){
            $builder->setQuery($data['query']);
        }
        if (isset($data['pageSize'])) {
            $builder->setPageSize($data['pageSize']);
        }
        if (isset($data['page'])) {
            $builder->setPage($data['page']);
        }
        if (isset($data['sortFields'])) {
            $builder->setSortFields($data['sortFields']);
        }
        if (isset($data['fields'])) {
            $builder->setResultFields($data['fields']);
        }

        $items = $builder->loadData($assocField);
        if (isset($data['total']) && $data['total']) {
            $result['total'] = $builder->getTotalCount();
        }

        $result['items'] = $items;
        return $result;
    }

    /**
     * @return ListBuilder
     */
    public function getListBuilder()
    {
        return $this->getDatabaseAdapter()->newListBuilder($this->getTable(), $this->getDescriptions(), $this->primaryKey);
    }

    /**
     * @return string
     */
    public function getCurrentDateTime()
    {
        $date = new \DateTime('now', new \DateTimeZone('Europe/Berlin'));
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * @param array $data
     * @return array
     */
    public function describe(array $data)
    {
        $result = [];
        foreach ($this->description as $description) {
            $desc = [
                'primary' => $description->isPrimaryKey(),
                'readonly' => $description->isReadOnly(),
                'type' => $description::TYPE,
                'options' => $description->getOptions(),
                'required' => $description->isRequired(),
                'default' => $description->getDefaultValue(),
                'operators' => $description->getSupportedOperators()
            ];
            $result[$description->getName()] = $desc;
        }
        return $result;
    }
}

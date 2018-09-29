<?php

namespace MicroShard\Core\JsonRpc;

use MicroShard\JsonRpcServer\HandlerInterface;
use MicroShard\JsonRpcServer\Request;
use MicroShard\Core\Data\Model;

class CrudHandler implements HandlerInterface
{
    const METHOD_CREATE = 'create';
    const METHOD_LOAD = 'load';
    const METHOD_UPDATE = 'update';
    const METHOD_DELETE = 'delete';
    const METHOD_LIST = 'list';
    const METHOD_DESCRIBE = 'describe';

    /**
     * @var Model
     */
    protected $model;

    /**
     * CrudHandler constructor.
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @return Model
     */
    protected function getModel()
    {
        return $this->model;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function handle(Request $request)
    {
        $result = [];
        switch ($request->getMethod()) {
            case self::METHOD_CREATE:
                $result = $this->create($request->getPayload());
                break;
            case self::METHOD_LOAD:
                $result = $this->load($request->getPayload());
                break;
            case self::METHOD_UPDATE:
                $result = $this->update($request->getPayload());
                break;
            case self::METHOD_DELETE:
                $result = $this->delete($request->getPayload());
                break;
            case self::METHOD_LIST:
                $result = $this->list($request->getPayload());
                break;
            case self::METHOD_DESCRIBE:
                $result = $this->describe($request->getPayload());
                break;
            default:
                $this->handleAdditionalMethods($request);
        }
        return $result;
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function handleAdditionalMethods(Request $request)
    {
        return [];
    }

    /**
     * @param array $payload
     * @return array
     */
    protected function create(array $payload)
    {
        return $this->getModel()->create($payload);
    }

    /**
     * @param array $payload
     * @return array
     */
    protected function load(array $payload)
    {
        return $this->getModel()->load($payload);
    }

    /**
     * @param array $payload
     * @return array
     */
    protected function update(array $payload)
    {
        return $this->getModel()->update($payload);
    }

    /**
     * @param array $payload
     * @return array
     */
    protected function delete(array $payload)
    {
        return $this->getModel()->delete($payload);
    }

    /**
     * @param array $payload
     * @return array
     */
    protected function list(array $payload)
    {
        return $this->getModel()->list($payload);
    }

    /**
     * @param array $payload
     * @return array
     */
    protected function describe(array $payload)
    {
        return $this->getModel()->describe($payload);
    }
}
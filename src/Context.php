<?php

namespace Luur\Validator;

use Luur\Exceptions\BranchNotFoundException;
use Luur\Travers;

class Context implements ContextInterface
{
    /**
     * @var Travers
     */
    protected $params;

    public function __construct($params = [])
    {
        $this->params = new Travers($params);
    }

    /**
     * @param string $key
     * @return mixed
     * @throws BranchNotFoundException
     */
    public function get($key)
    {
        return $this->params->find($key);
    }

    /**
     * @param $key
     * @param $value
     */
    public function set($key, $value)
    {
        $this->params->change($key, $value);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->params->getTree();
    }

    /**
     * @param array $params
     */
    public function setParams($params)
    {
        $this->params = new Travers($params);
    }
}

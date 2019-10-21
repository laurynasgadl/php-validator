<?php


namespace Luur\Validator;


interface ContextInterface
{
    /**
     * @param array $params
     */
    public function setParams(array $params);

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key);

    /**
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function set($key, $value);

    /**
     * @return array
     */
    public function toArray();
}

<?php
/**
 * Request
 * 
 * @author Sokolov Innokenty, <qbbr@qbbr.ru>
 * @copyright Copyright (c) 2010, qbbr
 */

class Q_Request implements ArrayAccess
{

    /**
     * хранилище
     * @var array
     */
    private $container = array();


    /**
     * конструктор
     *
     * @param array $array миссив с данными
     */
    public function __construct(array $array)
    {
        foreach ($array as $key => $value) {
            $this[$key] = $value;
        }
    }


    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }


    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }


    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }


    public function offsetUnset($offset) {
        return false;
    }


    public function __get($offset) {
        return $this->offsetGet($offset);
    }

}
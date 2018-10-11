<?php
/**
 * PHP FORMS API
 *
 * @package degami/php-forms-api
 */
/* #########################################################
   ####                      BASE                       ####
   ######################################################### */

namespace Degami\PHPFormsApi\Abstracts\Base;

use \Iterator;
use \ArrayIterator;
use \ArrayAccess;
use \Countable;

/**
 * a class to hold form fields submitted values
 */

abstract class DataBag implements Iterator, ArrayAccess, Countable
{

    /**
     * current position
     *
     * @var integer
     */
    protected $position = -1;

    /**
     * data to be stored
     *
     * @var array
     */
    protected $data = [];


    /**
     * class constructor
     *
     * @param mixed $data data to add
     */
    public function __construct($data)
    {
        $this->position = -1;
        $this->add($data);
    }

    /**
     * adds data to the element
     *
     * @param mixed $data data to add
     * @return DataBag
     */
    public function add($data)
    {
        if (!is_array($data)) {
            if (!empty($data)) {
                $data = [$data];
            } else {
                $data = [];
            }
        }
        foreach ($data as $k => $v) {
            if (is_numeric($k)) {
                $k = '_value'.$k;
            }
            $this->{$k} = $v;
        }
        return $this;
    }

    /**
     * delete data by key
     *
     * @param  string $key key of data to remove
     * @return DataBag
     */
    public function delete($key)
    {
        $this->offsetUnset($key);
        return $this;
    }

    /**
     * check if data is contained
     *
     * @param  string $key key of data to check
     * @return boolean
     */
    public function contains($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * rewind pointer position
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * get data keys
     *
     * @return array data keys
     */
    private function getKeys()
    {
        return array_keys($this->data);
    }

    /**
     * get current element
     *
     * @return mixed current element
     */
    public function current()
    {
        $keys = $this->getKeys();
        if (!isset($keys[$this->position])) {
            return false;
        }
        return $this->data[ $keys[$this->position] ];
    }

    /**
     * get current position key
     *
     * @return string key
     */
    public function key()
    {
        $keys = $this->getKeys();
        return $keys[ $this->position ];
    }

    /**
     * increment current position
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * check if current position is valud
     *
     * @return boolean current position is valid
     */
    public function valid()
    {
        $keys = $this->getKeys();
        if (!isset($keys[$this->position])) {
            return false;
        }
        return isset($this->data[ $keys[$this->position] ]);
    }

    /**
     * gets data by key
     *
     * @param  string $key key
     * @return mixed data
     */
    public function __get($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    /**
     * sets data
     *
     * @param string $key key
     * @param mixed $value data to set
     * @return DataBag
     * @throws \Exception
     */
    public function __set($key, $value)
    {
        if ($key == 'data' || $key == 'position') {
            throw new \Exception('Cannot define "'.$key.'" property');
        }
        $this->data[$key] = (is_array($value)) ? new static($value) : $value;
        return $this;
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * @param $name
     */
    public function __unset($name)
    {
        unset($this->data[$name]);
    }

    /**
     * __sleep magic method
     *
     * @return array
     */
    public function __sleep()
    {
        return ['data'];
    }

    /**
     * set_state magic method
     *
     * @param $an_array
     *
     * @return DataBag
     */
    public static function __set_state($an_array)
    {
        $obj = new static($an_array);
        return $obj;
    }

    /**
     * gets data iterator
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this);
    }

    /**
     * gets data keys
     *
     * @return array data keys
     */
    public function keys()
    {
        return $this->getKeys();
    }

    /**
     * set data by key
     *
     * @param  string $offset key
     * @param  mixed $value data to set
     */
    public function offsetSet($offset, $value)
    {
        return $this->__set($offset, $value);
    }

    /**
     * check if data exists bu key
     * @param  string $offset key to check
     * @return boolean data exists
     */
    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }

    /**
     * removes data by key
     *
     * @param  string $offset key to delete
     */
    public function offsetUnset($offset)
    {
        $this->__unset($offset);
    }

    /**
     * gets data by key
     *
     * @param  string $offset key to get
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /**
     * gets data as array
     *
     * @return array
     */
    public function toArray()
    {
        $out = [];
        $this->checkDataArr();
        foreach ($this->data as $key => $value) {
            $out[$key] = ($value instanceof DataBag) ? $value->toArray() : $value;
        }
        return $out;
    }

    /**
     * gets an array with the selected keys
     *
     * @param  array $keys keys to get
     * @return array
     */
    public function only(array $keys)
    {
        $out = [];
        if (empty($keys)) {
            return $this->toArray();
        }
        foreach ($this->toArray() as $k => $v) {
            if (in_array($k, $keys)) {
                $out[$k] = $v;
            }
        }
        return $out;
    }

    /**
     * gets data size
     *
     * @return integer
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * check "data" property to be an array
     * @return DataBag
     */
    protected function checkDataArr()
    {
        if (!is_array($this->data)) {
            if (!empty($this->data)) {
                $this->data = [ '_value0' => $this->data ];
            } else {
                $this->data = [];
            }
        }
        return $this;
    }
}

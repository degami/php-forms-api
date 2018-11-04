<?php
/**
 * PHP FORMS API
 * PHP Version 5.5
 *
 * @category Utils
 * @package  Degami\PHPFormsApi
 * @author   Mirko De Grandis <degami@github.com>
 * @license  MIT https://opensource.org/licenses/mit-license.php
 * @link     https://github.com/degami/php-forms-api
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
 * A class to hold form fields submitted values
 */

abstract class DataBag implements Iterator, ArrayAccess, Countable
{

    /**
     * Current position
     *
     * @var integer
     */
    protected $position = -1;

    /**
     * Data to be stored
     *
     * @var array
     */
    protected $data = [];

    /**
     * Prefix for numeric keys
     *
     * @var string
     */
    protected $numeric_keys_prefix = '_value';

    /**
     * Class constructor
     *
     * @param mixed $data data to add
     */
    public function __construct($data, $options = [])
    {
        if ($options == null) {
            $options = [];
        }

        unset($options['data']);
        unset($options['position']);

        foreach ($options as $name => $value) {
            $name = trim($name);
            if (property_exists(get_class($this), $name)) {
                $this->{$name} = $value;
            }
        }

        $this->position = -1;
        $this->add($data);
    }

    /**
     * Adds data to the element
     *
     * @param  mixed $data data to add
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
                $k = $this->numeric_keys_prefix.$k;
            }
            $this->{$k} = $v;
        }
        return $this;
    }

    /**
     * Delete data by key
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
     * Check if data is contained
     *
     * @param  string $key key of data to check
     * @return boolean
     */
    public function contains($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Rewind pointer position
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * Get data keys
     *
     * @return array data keys
     */
    private function getKeys()
    {
        return array_keys($this->data);
    }

    /**
     * Get data
     *
     * @return array data
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get current element
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
     * Get current position key
     *
     * @return string key
     */
    public function key()
    {
        $keys = $this->getKeys();
        return $keys[ $this->position ];
    }

    /**
     * Increment current position
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * Check if current position is valud
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
     * Gets data by key
     *
     * @param  string $key key
     * @return mixed data
     */
    public function __get($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    /**
     * Sets data
     *
     * @param  string $key   key
     * @param  mixed  $value data to set
     * @return DataBag
     * @throws \Exception
     */
    public function __set($key, $value)
    {
        if ($key == 'data' || $key == 'position') {
            throw new \Exception('Cannot define "'.$key.'" property');
        }
        $this->data[$key] = $value;
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
     * Set_state magic method
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
     * Gets data iterator
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this);
    }

    /**
     * Gets data keys
     *
     * @return array data keys
     */
    public function keys()
    {
        return $this->getKeys();
    }

    /**
     * Set data by key
     *
     * @param string $offset key
     * @param mixed  $value  data to set
     */
    public function offsetSet($offset, $value)
    {
        return $this->__set($offset, $value);
    }

    /**
     * Check if data exists bu key
     *
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
     * @param string $offset key to delete
     */
    public function offsetUnset($offset)
    {
        $this->__unset($offset);
    }

    /**
     * Gets data by key
     *
     * @param  string $offset key to get
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /**
     * Gets data as array
     *
     * @return array
     */
    public function toArray()
    {
        $out = [];
        $this->checkDataArr();
        foreach ($this->data as $key => $value) {
            $out[$key] = (is_object($value) && method_exists($value, 'toArray')) ?
                            $value->toArray() :
                            $value;
        }
        return $out;
    }

    /**
     * Gets an array with the selected keys
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
     * Gets data size
     *
     * @return integer
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * Check "data" property to be an array
     *
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

    /**
     * Drops "data" contents
     *
     * @return DataBag
     */
    public function clear()
    {
        foreach ($this->getKeys() as $key) {
            $this->__unset($key);
        }
        return $this;
    }
}

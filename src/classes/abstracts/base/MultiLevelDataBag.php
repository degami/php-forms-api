<?php
/**
 * Created by PhpStorm.
 * User: mirko
 * Date: 11/10/18
 * Time: 9.46
 */

namespace Degami\PHPFormsApi\Abstracts\Base;

class MultiLevelDataBag extends DataBag
{

    /**
     * element parent
     *
     * @var DataBag
     */
    protected $parent = null;

    /**
     * class constructor
     *
     * @param mixed $data data to add
     * @param mixed $parent element parent object
     */
    public function __construct($data, $parent = null)
    {
        $this->parent = $parent;
        parent::__construct($data);
    }

    /**
     * @return \Degami\PHPFormsApi\Abstracts\Base\DataBag
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param \Degami\PHPFormsApi\Abstracts\Base\DataBag $parent
     *
     * @return MultiLevelDataBag
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * sets data
     *
     * @param string $key key
     * @param mixed $value data to set
     * @return DataBag
     */
    public function __set($key, $value)
    {
        $this->data[$key] = (is_array($value)) ? new static($value, $this) : $value;
        return $this;
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
     * @return \Degami\PHPFormsApi\Abstracts\Base\MultiLevelDataBag
     */
    public static function __set_state($an_array)
    {
        $obj = new static($an_array);
        return $obj;
    }

    /**
     * data change notification on the tree
     */
    public function notifyChange()
    {
        if ($this->getParent() instanceof MultiLevelDataBag) {
            $this->getParent()->notifyChange();
        } else {
            $this->onChange();
        }
    }

    /**
     * data changed event hook
     */
    private function onChange()
    {
        // your code here
    }
}

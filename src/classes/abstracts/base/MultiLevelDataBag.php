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

abstract class MultiLevelDataBag extends DataBag
{
    /**
     * element parent
     *
     * @var DataBag
     */
    protected $parent = null;

    /**
     * Class constructor
     *
     * @param mixed $data   data to add
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
     * Sets data
     *
     * @param  string $key   key
     * @param  mixed  $value data to set
     * @return DataBag
     */
    public function __set($key, $value)
    {
        if ($key == 'data' || $key == 'position' || $key == 'parent') {
            throw new \Exception('Cannot define "'.$key.'" property');
        }
        $this->checkDataArr();
        $this->data[$key] = (is_array($value)) ? new static($value, $this) : $value;
        return $this;
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
    abstract public function onChange();
}

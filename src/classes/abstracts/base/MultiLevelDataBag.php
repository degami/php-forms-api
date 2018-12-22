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

use Degami\PHPFormsApi\Exceptions\FormException;

/**
 * A class to hold data
 *
 * @abstract
 */
abstract class MultiLevelDataBag extends DataBag
{
    /**
     * Element parent
     *
     * @var DataBag
     */
    protected $parent = null;

    /**
     * Class constructor
     *
     * @param mixed $data   data to add
     * @param DataBag $parent element parent object
     */
    public function __construct($data, $parent = null)
    {
        $this->parent = $parent;
        parent::__construct($data);
    }

    /**
     * Gets parent element
     *
     * @return \Degami\PHPFormsApi\Abstracts\Base\DataBag
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Sets parent element
     *
     * @param DataBag $parent
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
     * @return MultiLevelDataBag
     */
    public function __set($key, $value)
    {
        if ($key == 'data' || $key == 'position' || $key == 'parent') {
            throw new FormException('Cannot define "'.$key.'" property');
        }
        $this->checkDataArr();
        $this->data[$key] = (is_array($value)) ? new static($value, $this) : $value;
        $this->notifyChange();
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $key key of element to remove
     */
    public function __unset($key)
    {
        parent::__unset($key);
        $this->notifyChange();
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
     * ensures array tree is present as on path parameter
     *
     * @param  string $path      tree path
     * @param  string $delimiter delimiter
     * @return boolean
     */
    public function ensurePath($path, $delimiter = '/')
    {
        if (!is_string($path) || trim($path) == '') {
            return false;
        }
        $path = explode($delimiter, $path);
        $ptr = &$this;
        if (!is_array($path)) {
            $path = [$path];
        }
        foreach ($path as $key => $value) {
            if (trim($value) == '') {
                continue;
            }
            if (!isset($ptr->{$value})) {
                $ptr->{$value} = [];
            }
            $ptr = &$ptr->{$value};
        }
        return true;
    }

    /**
     * data changed event hook
     */
    abstract public function onChange();
}

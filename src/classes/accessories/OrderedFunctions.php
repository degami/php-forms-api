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
   ####                 ACCESSORIES                     ####
   ######################################################### */

namespace Degami\PHPFormsApi\Accessories;

use Degami\Basics\DataBag;

/**
 * class for maintaining ordered list of functions
 */
class OrderedFunctions extends DataBag
{

    /** @var null|string sort function name */
    private $sort_callback = null;

    /** @var string type */
    private $type;

    /**
     * Class constructor
     *
     * @param array  $array         initially contained elements
     * @param string $type          type of elements
     * @param string $sort_callback sort callback name
     */
    public function __construct(array $array, $type, $sort_callback = null)
    {
        parent::__construct($array);
        $this->type = $type;
        $this->sort_callback = $sort_callback;
        $this->sort();
    }

    /**
     * sort elements
     */
    public function sort()
    {
        foreach ($this->dataelement_data as &$value) {
            if (is_string($value)) {
                $value = strtolower(trim($value));
            } elseif (is_array($value) && isset($value[$this->type])) {
                $value[$this->type] = strtolower(trim($value[$this->type]));
            }
        }

        $this->dataelement_data = array_unique($this->dataelement_data, SORT_REGULAR);

        if (!empty($this->sort_callback) && is_callable($this->sort_callback)) {
            usort($this->dataelement_data, $this->sort_callback);
        }
    }

    /**
     * rewind pointer position
     */
    public function rewind()
    {
        parent::rewind();
        $this->sort();
    }

    /**
     * Check if element is present
     *
     * @param  mixed $value value to search
     * @return boolean       TRUE if $value was found
     */
    public function hasValue($value)
    {
        return in_array($value, $this->getValues());
    }

    /**
     * Check if key is in the array keys
     *
     * @param  integer $key key to search
     * @return boolean       TRUE if key was found
     */
    public function hasKey($key)
    {
        return in_array($key, array_keys($this->dataelement_data));
    }

    /**
     * Return element values
     *
     * @return array element values
     */
    public function getValues()
    {
        $out = [];
        foreach ($this->dataelement_data as $key => $value) {
            if (is_array($value) && isset($value[$this->type])) {
                $out[] = $value[$this->type];
            } else {
                $out[] = $value;
            }
        }
        return $out;
    }

    /**
     * Adds a new element to array elements
     *
     * @param mixed $value element to add
     */
    public function addElement($value)
    {
        $this->dataelement_data[] = $value;
        $this->sort();
    }

    /**
     * removes an element from array elements
     *
     * @param mixed $value element to remove
     */
    public function removeElement($value)
    {
        $this->dataelement_data = array_diff($this->dataelement_data, [$value]);
        $this->sort();
    }

    /**
     * Element to array
     *
     * @return array element to array
     */
    public function toArray()
    {
        return $this->dataelement_data;
    }
}

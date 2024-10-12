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

    /** @var array|null */
    protected array $dataelement_data = [];

    /**
     * Class constructor
     *
     * @param array $array initially contained elements
     * @param string $type type of elements
     * @param callable $sort_callback sort callback name
     */
    public function __construct(array $array, string $type, $sort_callback = null)
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
    public function rewind() : void
    {
        parent::rewind();
        $this->sort();
    }

    /**
     * Check if element is present
     *
     * @param  mixed $value value to search
     * @return bool       TRUE if $value was found
     */
    public function hasValue($value): bool
    {
        return in_array($value, $this->getValues());
    }

    /**
     * Check if key is in the array keys
     *
     * @param int $key key to search
     * @return bool       TRUE if key was found
     */
    public function hasKey(int $key): bool
    {
        return in_array($key, array_keys($this->dataelement_data));
    }

    /**
     * Return element values
     *
     * @return mixed element values
     */
    public function getValues(): array
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
     * @return self
     */
    public function addElement($value): OrderedFunctions
    {
        $this->dataelement_data[] = $value;
        $this->sort();

        return $this;
    }

    /**
     * removes an element from array elements
     *
     * @param mixed $value element to remove
     * @return self
     */
    public function removeElement($value): OrderedFunctions
    {
        $this->dataelement_data = array_diff($this->dataelement_data, [$value]);
        $this->sort();

        return $this;
    }

    /**
     * Element to array
     *
     * @return array element to array
     */
    public function toArray(): array
    {
        return $this->dataelement_data;
    }
}

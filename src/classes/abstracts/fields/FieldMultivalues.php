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
   ####                    FIELDS                       ####
   ######################################################### */

namespace Degami\PHPFormsApi\Abstracts\Fields;

use Degami\PHPFormsApi\Abstracts\Base\Field;
use Degami\PHPFormsApi\Fields\Option;
use Degami\PHPFormsApi\Fields\Optgroup;
use Degami\PHPFormsApi\Abstracts\Fields\Optionable;

/**
 * The multivalues field class (a select, a radios or a checkboxes group)
 *
 * @abstract
 */
abstract class FieldMultivalues extends Field
{
    /**
     * options array
     *
     * @var array
     */
    protected $options = [];

    /**
     * Adds an option to options array
     *
     * @param mixed $option option
     */
    public function addOption($option)
    {
        $this->options[] = $option;
    }

    /**
     * Get elements options array by reference
     *
     * @return array element options
     */
    public function &getOptions()
    {
        return $this->options;
    }

    /**
     * Check if key is present into haystack
     *
     * @param  mixed $needle   element to find
     * @param  array $haystack where to find it
     * @return boolean           TRUE if element is found
     */
    public static function hasKey($needle, $haystack)
    {
        foreach ($haystack as $key => $value) {
            if ($value instanceof Option) {
                if ($value->getKey() == $needle) {
                    return true;
                }
            } elseif ($value instanceof Optgroup) {
                if ($value->optionsHasKey($needle) == true) {
                    return true;
                }
            } elseif ($needle == $key) {
                return true;
            } elseif (FieldMultivalues::isForeacheable($value) && FieldMultivalues::hasKey($needle, $value) == true) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if key is present into element options
     *
     * @param  mixed $needle element to find
     * @return boolean TRUE if element is found
     */
    public function optionsHasKey($needle)
    {
        return FieldMultivalues::hasKey($needle, $this->options);
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean TRUE if element is valid
     */
    public function isValid()
    {
        $titlestr = (!empty($this->title)) ? $this->title : !empty($this->name) ? $this->name : $this->id;

        if (!is_array($this->value) && !empty($this->value)) {
            $check = $this->optionsHasKey($this->value);
            $this->addError(str_replace("%t", $titlestr, $this->getText("%t: Invalid choice")), __FUNCTION__);

            if (!$check) {
                return false;
            }
        } elseif (FieldMultivalues::isForeacheable($this->value)) {
            $check = true;
            foreach ($this->value as $key => $value) {
                $check &= $this->optionsHasKey($value);
            }
            if (!$check) {
                $this->addError(str_replace("%t", $titlestr, $this->getText("%t: Invalid choice")), __FUNCTION__);

                if ($this->stop_on_first_error) {
                    return false;
                }
            }
        }
        return parent::isValid();
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean this is a value
     */
    public function isAValue()
    {
        return true;
    }
}

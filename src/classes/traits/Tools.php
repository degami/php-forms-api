<?php
/**
 * PHP FORMS API
 *
 * @category Utils
 * @package  Degami\PHPFormsApi
 * @author   Mirko De Grandis <degami@github.com>
 * @license  MIT https://opensource.org/licenses/mit-license.php
 * @link     https://github.com/degami/php-forms-api
 */
/* #########################################################
   ####                     TRAITS                      ####
   ######################################################### */

namespace Degami\PHPFormsApi\Traits;

use Degami\PHPFormsApi\Abstracts\Base\Element;

/**
 * tools functions
 */
trait Tools
{
    /**
     * scan_array private method
     *
     * @param string $string string to search
     * @param array $array array to check
     * @return mixed|bool          found element / FALSE on failure
     */
    private static function scanArray(string $string, array $array)
    {
        list($key, $rest) = preg_split('/[[\]]/', $string, 2, PREG_SPLIT_NO_EMPTY);
        if ($key && $rest) {
            return call_user_func_array([__CLASS__, 'scanArray'], [$rest, $array[$key]]);
        } elseif ($key && isset($array[$key])) {
            return $array[$key];
        }
        return false;
    }

    /**
     * applies array_flatten to array
     *
     * @param array $array array to flatten
     * @return array        monodimensional array
     */
    public static function arrayFlatten(array $array): array
    {
        $return = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $return = array_merge(
                    $return,
                    call_user_func_array([__CLASS__,'arrayFlatten'], [$value])
                );
            } else {
                $return[$key] = $value;
            }
        }
        return $return;
    }

    /**
     * Get array values by key
     *
     * @param ?string $search_key key to search
     * @param array $array where to search
     * @return array              the filtered array
     */
    public static function arrayGetValues(?string $search_key, array $array): array
    {
        $return = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $return = array_merge(
                    $return,
                    call_user_func_array([__CLASS__,'arrayGetValues'], [ $search_key, $value ])
                );
            } elseif ($key == $search_key) {
                $return[] = $value;
            }
        }
        return $return;
    }

    /**
     * order elements by weight properties
     *
     * @param Element $a first element
     * @param Element $b second element
     * @return int    position
     */
    public static function orderByWeight(Element $a, Element $b): int
    {
        if ($a->getWeight() == $b->getWeight()) {
            return 0;
        }
        return ($a->getWeight() < $b->getWeight()) ? -1 : 1;
    }

    /**
     * order validation functions
     *
     * @param array $a first element
     * @param array $b second element
     * @return int    position
     */
    public static function orderValidators(array $a, array $b): int
    {
        if (is_array($a) && isset($a['validator'])) {
            $a = $a['validator'];
        }
        if (is_array($b) && isset($b['validator'])) {
            $b = $b['validator'];
        }

        if ($a == $b) {
            return 0;
        }
        if ($a == 'required') {
            return -1;
        }
        if ($b == 'required') {
            return 1;
        }

        return 0;
        //    return $a > $b ? 1 : -1;
    }

    /**
     * translate strings, using a function named "__()" if is defined.
     * The function should take a string written in english as parameter and return the translated version
     *
     * @param string $string string to translate
     * @return string         the translated version
     */
    public static function translateString(string $string): string
    {
        if (is_string($string) && function_exists('__')) {
            return __($string);
        }
        return $string;
    }

    /**
     * Returns the translated version of the input text ( when available ) depending on current element configuration
     *
     * @param string $text input text
     * @return string       text to return (translated or not)
     */
    protected function getText(string $text): string
    {
        if ($this->no_translation == true) {
            return $text;
        }
        return call_user_func_array([__CLASS__, 'translateString'], [$text]);
    }

    /**
     * Get a string representing the called class
     *
     * @return string
     */
    public static function getClassNameString(): string
    {
        $basename = basename(strtolower(str_replace("\\", "/", get_called_class())));
        $parentname = basename(dirname(strtolower(str_replace("\\", "/", get_called_class()))));

        return $basename.'_'.preg_replace("/s$/", "", $parentname);
    }

    /**
     * Check if a function name in the "user" space match the regexp
     * and if found executes it passing the arguments
     *
     * @param string $regexp regular Expression to find function
     * @param mixed $args arguments array
     */
    public static function executeAlter(string $regexp, $args)
    {
        $defined_functions = get_defined_functions();
        if (!is_array($args)) {
            $args = [ $args ];
        }
        foreach ($defined_functions['user'] as $function_name) {
            if (preg_match($regexp, $function_name)) {
                call_user_func_array($function_name, $args);
            }
        }
    }
}

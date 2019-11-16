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
   ####                     TRAITS                      ####
   ######################################################### */

namespace Degami\PHPFormsApi\Traits;

/**
 * tools functions
 */
trait Tools
{

    /**
     * Set class properties. Used on constructors
     *
     * @param array $options values to set
     */
    private function setClassProperties($options)
    {
        foreach ($options as $name => $value) {
            $name = trim($name);
            if (property_exists(get_class($this), $name)) {
                $this->{$name} = $value;
            }
        }
    }

    /**
     * format byte size
     *
     * @param  integer $size size in bytes
     * @return string       formatted size
     */
    public static function formatBytes($size)
    {
        $units = [' B', ' KB', ' MB', ' GB', ' TB'];
        for ($i = 0; $size >= 1024 && $i < 4; $i++) {
            $size /= 1024;
        }
        return round($size, 2).$units[$i];
    }

    /**
     * scan_array private method
     *
     * @param  string $string string to search
     * @param  array  $array  array to check
     * @return mixed          found element / FALSE on failure
     */
    private static function scanArray($string, $array)
    {
        list($key, $rest) = preg_split('/[[\]]/', $string, 2, PREG_SPLIT_NO_EMPTY);
        if ($key && $rest) {
            return call_user_func_array([__CLASS__, 'scanArray'], [$rest, $array[$key]]);
        } elseif ($key) {
            return $array[$key];
        } else {
            return false;
        }
    }

    /**
     * applies array_flatten to array
     *
     * @param  array $array array to flatten
     * @return array        monodimensional array
     */
    public static function arrayFlatten($array)
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
     * @param  string $search_key key to search
     * @param  array  $array      where to search
     * @return array              the filtered array
     */
    public static function arrayGetValues($search_key, $array)
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
     * @param  \Degami\PHPFormsApi\Abstracts\Base\Element $a first element
     * @param  \Degami\PHPFormsApi\Abstracts\Base\Element $b second element
     * @return int    position
     */
    public static function orderByWeight($a, $b)
    {
        if ($a->getWeight() == $b->getWeight()) {
            return 0;
        }
        return ($a->getWeight() < $b->getWeight()) ? -1 : 1;
    }

    /**
     * order validation functions
     *
     * @param  array $a first element
     * @param  array $b second element
     * @return int    position
     */
    public static function orderValidators($a, $b)
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
     * @param  string $string string to translate
     * @return string         the translated version
     */
    public static function translateString($string)
    {
        if (is_string($string) && function_exists('__')) {
            return __($string);
        }
        return $string;
    }

    /**
     * Returns the translated version of the input text ( when available ) depending on current element configuration
     *
     * @param  string $text input text
     * @return string       text to return (translated or not)
     */
    protected function getText($text)
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
    public static function getClassNameString()
    {
        $called_class = array_map(
            "strtolower",
            explode(
                "\\",
                preg_replace(
                    "/^Degami\\\\PHPFormsApi\\\\(.*?)$/i",
                    "\\1",
                    get_called_class()
                ),
                2
            )
        );
        return $called_class[1]."_".preg_replace("/s$/", "", $called_class[0]);
    }

    /**
     * Check if a function name in the "user" space match the regexp
     * and if found executes it passing the arguments
     *
     * @param string $regexp regular Expression to find function
     * @param mixed  $args   arguments array
     */
    public static function executeAlter($regexp, $args)
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

    /**
     * Checks if variable is suitable for use with foreach
     *
     * @param  mixed $var element to check
     * @return bool
     */
    public static function isForeacheable($var)
    {
        return (is_array($var) || ($var instanceof \Traversable));
    }

    /**
     * Take a string_like_this and return a StringLikeThis
     *
     * @param  string
     * @return string
     */
    public static function snakeCaseToPascalCase($input)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $input)));
    }

    /**
     * Take a StringLikeThis and return string_like_this
     *
     * @param  string
     * @return string
     */
    public function pascalCaseToSnakeCase($input)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $ret);
    }
}

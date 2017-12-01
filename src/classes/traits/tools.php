<?php
/**
 * PHP FORMS API
 * @package degami/php-forms-api
 */
/* #########################################################
   ####                     TRAITS                      ####
   ######################################################### */

namespace Degami\PHPFormsApi\Traits;

use Degami\PHPFormsApi\form;

trait tools {
	/**
	 * format byte size
	 * @param  integer $size size in bytes
	 * @return string       formatted size
	 */
	private static function format_bytes($size) {
		$units = [' B', ' KB', ' MB', ' GB', ' TB'];
		for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
			return round($size, 2).$units[$i];
	}

	/**
	 * scan_array private method
	 * @param  string $string string to search
	 * @param  array $array   array to check
	 * @return mixed          found element / FALSE on failure
	 */
	private static function scan_array($string, $array) {
		list($key, $rest) = preg_split('/[[\]]/', $string, 2, PREG_SPLIT_NO_EMPTY);
		if ( $key && $rest ) {
			return @form::scan_array($rest, $array[$key]);
		} elseif ( $key ) {
			return $array[$key];
		} else {
			return FALSE;
		}
	}

	/**
	* applies array_flatten to array
	* @param  array $array array to flatten
	* @return array        monodimensional array
	*/
	public static function array_flatten($array) {
		$return = [];
		foreach ($array as $key => $value) {
			if (is_array($value)){
				$return = array_merge($return, form::array_flatten($value));
			} else {
				$return[$key] = $value;
			}
		}
		return $return;
	}

	/**
	* get array values by key
	* @param  string $search_key key to search
	* @param  array $array       where to search
	* @return array              the filtered array
	*/
	public static function array_get_values($search_key, $array) {
		$return = [];
		foreach ($array as $key => $value) {
			if (is_array($value)){
				$return = array_merge($return, form::array_get_values($search_key, $value));
			}else if($key == $search_key){
				$return[] = $value;
			}
		}
		return $return;
	}

	/**
	* order elements by weight properties
	* @param  element $a first element
	* @param  element $b second element
	* @return int    position
	*/
	public static function order_by_weight($a, $b){
		if ($a->get_weight() == $b->get_weight()) {
			return 0;
		}
		return ($a->get_weight() < $b->get_weight()) ? -1 : 1;
	}

	/**
	* order validation functions
	* @param  array $a first element
	* @param  array $b second element
	* @return int    position
	*/
	public static function order_validators($a,$b){
		if(is_array($a) && isset($a['validator'])) $a = $a['validator'];
		if(is_array($b) && isset($b['validator'])) $b = $b['validator'];

		if($a == $b) return 0;
		if($a == 'required') return -1;
		if($b == 'required') return 1;

		return 0;
	//    return $a > $b ? 1 : -1;
	}

	/**
	* translate strings, using a function named "__()" if is defined.
	* the function should take a string written in english as parameter and return the translated version
	* @param  string $string string to translate
	* @return string         the translated version
	*/
	public static function translate_string($string){
		if(is_string($string) && function_exists('__')) return __($string);
		return $string;
	}

}

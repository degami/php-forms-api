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

/**
 * validations functions
 */
trait validators{

    /**
     * "required" validation function
     * @param  mixed $value the element value
     * @return mixed        TRUE if valid or a string containing the error message
     */
    public static function validate_required($value = NULL) {
      if ( !empty($value) || (!is_array($value) && trim($value) != '') ) {
        return TRUE;
      } else {
        return "<em>%t</em> is required";
      }
    }

    /**
     * "notZero" required validation function - useful for radios
     * @param  mixed $value the element value
     * @return mixed        TRUE if valid or a string containing the error message
     */
    public static function validate_notzero($value = NULL) {
      if ( (!empty($value) && (!is_array($value) && trim($value) != '0')) ) {
        return TRUE;
      } else {
        return "<em>%t</em> is required";
      }
    }

    /**
     * "max_length" validation function
     * @param  mixed $value   the element value
     * @param  mixed $options max length
     * @return mixed        TRUE if valid or a string containing the error message
     */
    public static function validate_max_length($value, $options) {
      // if(!is_string($value)) throw new Exception("Invalid value - max_length is meant for strings, ".gettype($value)." given");
      if (strlen($value) > $options) {
        return "Maximum length of <em>%t</em> is {$options}";
      }
      return TRUE;
    }

    /**
     * "min_length" validation function
     * @param  mixed $value   the element value
     * @param  mixed $options min length
     * @return mixed        TRUE if valid or a string containing the error message
     */
    public static function validate_min_length($value, $options) {
      // if(!is_string($value)) throw new Exception("Invalid value - min_length is meant for strings, ".gettype($value)." given");
      if (strlen($value) < $options) {
        return "<em>%t</em> must be longer than {$options}";
      }
      return TRUE;
    }

    /**
     * "exact_length" validation function
     * @param  mixed $value   the element value
     * @param  mixed $options length
     * @return mixed        TRUE if valid or a string containing the error message
     */
    public static function validate_exact_length($value, $options) {
      // if(!is_string($value)) throw new Exception("Invalid value - exact_length is meant for strings, ".gettype($value)." given");
      if (strlen($value) != $options) {
        return "<em>%t</em> must be {$options} characters long.";
      }
      return TRUE;
    }

    /**
     * "regexp" validation function
     * @param  mixed $value   the element value
     * @param  mixed $options regexp string
     * @return mixed        TRUE if valid or a string containing the error message
     */
    public static function validate_regexp($value, $options) {
      if (!preg_match( $options, $value)) {
        return "<em>%t</em> must match the regular expression \"$options\".";
      }
      return TRUE;
    }

    /**
     * "alpha" validation function
     * @param  mixed $value   the element value
     * @return mixed        TRUE if valid or a string containing the error message
     */
    public static function validate_alpha($value) {
      // if(!is_string($value)) throw new Exception("Invalid value - alpha is meant for strings, ".gettype($value)." given");
      if (!preg_match( "/^([a-z])+$/i", $value)) {
        return "<em>%t</em> must contain alphabetic characters.";
      }
      return TRUE;
    }

    /**
     * "alpha_numeric" validation function
     * @param  mixed $value   the element value
     * @return mixed        TRUE if valid or a string containing the error message
     */
    public static function validate_alpha_numeric($value) {
      // if(!is_string($value) && !is_numeric($value)) throw new Exception("Invalid value - alpha_numeric is meant for strings or numeric values, ".gettype($value)." given");
      if (!preg_match("/^([a-z0-9])+$/i", $value)) {
        return "<em>%t</em> must only contain alpha numeric characters.";
      }
      return TRUE;
    }

    /**
     * "alpha_dash" validation function
     * @param  mixed $value   the element value
     * @return mixed        TRUE if valid or a string containing the error message
     */
    public static function validate_alpha_dash($value) {
      // if(!is_string($value)) throw new Exception("Invalid value - alpha_dash is meant for strings, ".gettype($value)." given");
      if (!preg_match("/^([-a-z0-9_-])+$/i", $value)) {
        return "<em>%t</em> must contain only alpha numeric characters, underscore, or dashes";
      }
      return TRUE;
    }

    /**
     * "numeric" validation function
     * @param  mixed $value   the element value
     * @return mixed        TRUE if valid or a string containing the error message
     */
    public static function validate_numeric($value) {
      if (!is_numeric($value)) {
        return "<em>%t</em> must be numeric.";
      }
      return TRUE;
    }

    /**
     * "integer" validation function
     * @param  mixed $value   the element value
     * @return mixed        TRUE if valid or a string containing the error message
     */
    public static function validate_integer($value) {
      if (!preg_match( '/^[\-+]?[0-9]+$/', $value)) {
        return "<em>%t</em> must be an integer.";
      }
      return TRUE;
    }

    /**
     * "match" validation function
     * @param  mixed $value   the element value
     * @param  mixed $options elements to find into _REQUEST array
     * @return mixed        TRUE if valid or a string containing the error message
     */
    public static function validate_match($value, $options) {
      $other = call_user_func_array([__CLASS__, 'scan_array'], [$options, $_REQUEST]);
      if ($value != $other) {
        return "The field <em>%t</em> is invalid.";
      }
      return TRUE;
    }

    /**
     * "file_extension" validation function
     * @param  mixed $value   the element value
     * @param  mixed $options file extension
     * @return mixed        TRUE if valid or a string containing the error message
     */
    public static function validate_file_extension($value, $options) {
      if(!isset($value['filepath'])) return "<em>%t</em> - Error. value has no filepath attribute";
      $options = explode(',', $options);
      $ext = substr(strrchr($value['filepath'], '.'), 1);
      if (!in_array($ext, $options)) {
        return "File upload <em>%t</em> is not of required type";
      }
      return TRUE;
    }

    /**
     * "file_not_exists" validation function
     * @param  mixed $value   the element value
     * @return mixed        TRUE if valid or a string containing the error message
     */
    public static function validate_file_not_exists($value) {
      if(!isset($value['filepath'])) return "<em>%t</em> - Error. value has no filepath attribute";
      if (file_exists($value['filepath'])) {
        return "The file <em>%t</em> has already been uploaded";
      }
      return TRUE;
    }

    /**
     * "max_file_size" validation function
     * @param  mixed $value   the element value
     * @param  mixed $options max file size
     * @return mixed        TRUE if valid or a string containing the error message
     */
    public static function validate_max_file_size($value, $options) {
      if(!isset($value['filesize'])) return "<em>%t</em> - Error. value has no filesize attribute";
      if ($value['filesize'] > $options) {
        $max_size = call_user_func_array([__CLASS__, 'format_bytes'], [$options]);
        return "The file <em>%t</em> is too big. Maximum filesize is {$max_size}.";
      }
      return TRUE;
    }

    /**
     * "is_date" validation function
     * @param  mixed $value   the element value
     * @return mixed        TRUE if valid or a string containing the error message
     */
    public static function validate_is_date($value) {
      if ( !$value || ($value && ($date = date_create($value)) === false) ) {
        return "<em>%t</em> is not a valid date.";
      }
      return TRUE;
    }

    /**
     * "email" validation function
     * @param  mixed $value   the element value
     * @return mixed        TRUE if valid or a string containing the error message
     */
    public static function validate_email($email) {
      if (empty($email)) return FALSE;
      $check_dns = FORMS_VALIDATE_EMAIL_DNS;
      $blocked_domains = explode('|', FORMS_VALIDATE_EMAIL_BLOCKED_DOMAINS);
      $atIndex = strrpos($email, "@");
      if (is_bool($atIndex) && !$atIndex) {
        return "<em>%t</em> is not a valid email. It must contain the @ symbol.";
      } else {
        $domain = substr($email, $atIndex+1);
        $local = substr($email, 0, $atIndex);
        $localLen = strlen($local);
        $domainLen = strlen($domain);
        if ($localLen < 1 || $localLen > 64) {
          return "<em>%t</em> is not a valid email. Local part is wrong length.";
        } else if ($domainLen < 1 || $domainLen > 255) {
          return "<em>%t</em> is not a valid email. Domain name is wrong length.";
        } else if ($local[0] == '.' || $local[$localLen-1] == '.') {
          return "<em>%t</em> is not a valid email. Local part starts or ends with '.'";
        } else if (preg_match('/\\.\\./', $local)) {
          return "<em>%t</em> is not a valid email. Local part two consecutive dots.";
        } else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
          return "<em>%t</em> is not a valid email. Invalid character in domain.";
        } else if (preg_match('/\\.\\./', $domain)) {
          return "<em>%t</em> is not a valid email. Domain name has two consecutive dots.";
        } else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local))) {
          if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\","",$local))) {
            return "<em>%t</em> is not a valid email. Invalid character in local part.";
          }
        }
        if (in_array($domain, $blocked_domains)) {
          return "<em>%t</em> is not a valid email. Domain name is in list of disallowed domains.";
        }
        if ($check_dns && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A"))) {
          return "<em>%t</em> is not a valid email. Domain name not found in DNS.";
        }
      }
      return TRUE;
    }

    /**
     * "is_RGB" validation function
     * @param  mixed $value   the element value
     * @return mixed        TRUE if valid or a string containing the error message
     */
    public static function validate_rgb($value) {
      if ( !$value || ($value && !preg_match("/^#?([a-f\d]{3}([a-f\d]{3})?)$/i", $value)) ) {
        return "<em>%t</em> is not a valid RGB color string.";
      }
      return TRUE;
    }

    /**
     * "is_URL" validation function
     * @param  mixed $value   the element value
     * @return mixed        TRUE if valid or a string containing the error message
     */
    public static function validate_url($value) {

      $URL_FORMAT = 
      '/^(https?):\/\/'.                                         // protocol
      '(([a-z0-9$_\.\+!\*\'\(\),;\?&=-]|%[0-9a-f]{2})+'.         // username
      '(:([a-z0-9$_\.\+!\*\'\(\),;\?&=-]|%[0-9a-f]{2})+)?'.      // password
      '@)?(?#'.                                                  // auth requires @
      ')((([a-z0-9]\.|[a-z0-9][a-z0-9-]*[a-z0-9]\.)*'.                      // domain segments AND
      '[a-z][a-z0-9-]*[a-z0-9]'.                                 // top level domain  OR
      '|((\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])\.){3}'.
      '(\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])'.                 // IP address
      ')(:\d+)?'.                                                // port
      ')(((\/+([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)*'. // path
      '(\?([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)'.      // query string
      '?)?)?'.                                                   // path and query string optional
      '(#([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)?'.      // fragment
      '$/i';

      if ( !$value || ($value && !preg_match($URL_FORMAT, $value)) ) {
        return "<em>%t</em> is not a valid URL string.";
      }
      return TRUE;
    }
}

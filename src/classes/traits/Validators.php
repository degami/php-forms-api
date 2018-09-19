<?php
/**
 * PHP FORMS API
 *
 * @package degami/php-forms-api
 */
/* #########################################################
   ####                     TRAITS                      ####
   ######################################################### */

namespace Degami\PHPFormsApi\Traits;

/**
 * validations functions
 */
trait Validators
{

    /**
     * "required" validation function
     *
     * @param  mixed $value the element value
     * @return mixed        TRUE if valid or a string containing the error message
     */
    public static function validateRequired($value = null)
    {
        if (!empty($value) || (!is_array($value) && trim($value) != '')) {
            return true;
        } else {
            return "<em>%t</em> is required";
        }
    }

    /**
     * "notZero" required validation function - useful for radios
     *
     * @param  mixed $value the element value
     * @return mixed        TRUE if valid or a string containing the error message
     */
    public static function validateNotzero($value = null)
    {
        if ((!empty($value) && (!is_array($value) && trim($value) != '0'))) {
            return true;
        } else {
            return "<em>%t</em> is required";
        }
    }

    /**
     * "max_length" validation function
     *
     * @param  mixed $value   the element value
     * @param  mixed $options max length
     * @return mixed        TRUE if valid or a string containing the error message
     */
    public static function validateMaxLength($value, $options)
    {
        if (strlen($value) > $options) {
            return "Maximum length of <em>%t</em> is {$options}";
        }
        return true;
    }

    /**
     * "min_length" validation function
     *
     * @param  mixed $value   the element value
     * @param  mixed $options min length
     * @return mixed        TRUE if valid or a string containing the error message
     */
    public static function validateMinLength($value, $options)
    {
        if (strlen($value) < $options) {
            return "<em>%t</em> must be longer than {$options}";
        }
        return true;
    }

    /**
     * "exact_length" validation function
     *
     * @param  mixed $value   the element value
     * @param  mixed $options length
     * @return mixed        TRUE if valid or a string containing the error message
     */
    public static function validateExactLength($value, $options)
    {
        if (strlen($value) != $options) {
            return "<em>%t</em> must be {$options} characters long.";
        }
        return true;
    }

    /**
     * "regexp" validation function
     *
     * @param  mixed $value   the element value
     * @param  mixed $options regexp string
     * @return mixed        TRUE if valid or a string containing the error message
     */
    public static function validateRegexp($value, $options)
    {
        if (!preg_match($options, $value)) {
            return "<em>%t</em> must match the regular expression \"$options\".";
        }
        return true;
    }

    /**
     * "alpha" validation function
     *
     * @param  mixed $value the element value
     * @return mixed        TRUE if valid or a string containing the error message
     */
    public static function validateAlpha($value)
    {
        if (!preg_match("/^([a-z])+$/i", $value)) {
            return "<em>%t</em> must contain alphabetic characters.";
        }
        return true;
    }

    /**
     * "alpha_numeric" validation function
     *
     * @param  mixed $value the element value
     * @return mixed        TRUE if valid or a string containing the error message
     */
    public static function validateAlphaNumeric($value)
    {
        if (!preg_match("/^([a-z0-9])+$/i", $value)) {
            return "<em>%t</em> must only contain alpha numeric characters.";
        }
        return true;
    }

    /**
     * "alpha_dash" validation function
     *
     * @param  mixed $value the element value
     * @return mixed        TRUE if valid or a string containing the error message
     */
    public static function validateAlphaDash($value)
    {
        if (!preg_match("/^([-a-z0-9_-])+$/i", $value)) {
            return "<em>%t</em> must contain only alpha numeric characters, underscore, or dashes";
        }
        return true;
    }

    /**
     * "numeric" validation function
     *
     * @param  mixed $value the element value
     * @return mixed        TRUE if valid or a string containing the error message
     */
    public static function validateNumeric($value)
    {
        if (!is_numeric($value)) {
            return "<em>%t</em> must be numeric.";
        }
        return true;
    }

    /**
     * "integer" validation function
     *
     * @param  mixed $value the element value
     * @return mixed        TRUE if valid or a string containing the error message
     */
    public static function validateInteger($value)
    {
        if (!preg_match('/^[\-+]?[0-9]+$/', $value)) {
            return "<em>%t</em> must be an integer.";
        }
        return true;
    }

    /**
     * "match" validation function
     *
     * @param  mixed $value   the element value
     * @param  mixed $options elements to find into _REQUEST array
     * @return mixed        TRUE if valid or a string containing the error message
     */
    public static function validateMatch($value, $options)
    {
        $other = call_user_func_array([__CLASS__, 'scan_array'], [$options, $_REQUEST]);
        if ($value != $other) {
            return "The field <em>%t</em> is invalid.";
        }
        return true;
    }

    /**
     * "file_extension" validation function
     *
     * @param  mixed $value   the element value
     * @param  mixed $options file extension
     * @return mixed        TRUE if valid or a string containing the error message
     */
    public static function validateFileExtension($value, $options)
    {
        if (!isset($value['filepath'])) {
            return "<em>%t</em> - Error. value has no filepath attribute";
        }
        $options = explode(',', $options);
        $ext = substr(strrchr($value['filepath'], '.'), 1);
        if (!in_array($ext, $options)) {
            return "File upload <em>%t</em> is not of required type";
        }
        return true;
    }

    /**
     * "file_not_exists" validation function
     *
     * @param  mixed $value the element value
     * @return mixed        TRUE if valid or a string containing the error message
     */
    public static function validateFileNotExists($value)
    {
        if (!isset($value['filepath'])) {
            return "<em>%t</em> - Error. value has no filepath attribute";
        }
        if (file_exists($value['filepath'])) {
            return "The file <em>%t</em> has already been uploaded";
        }
        return true;
    }

    /**
     * "max_file_size" validation function
     *
     * @param  mixed $value   the element value
     * @param  mixed $options max file size
     * @return mixed        TRUE if valid or a string containing the error message
     */
    public static function validateMaxFileSize($value, $options)
    {
        if (!isset($value['filesize'])) {
            return "<em>%t</em> - Error. value has no filesize attribute";
        }
        if ($value['filesize'] > $options) {
            $max_size = call_user_func_array([__CLASS__, 'format_bytes'], [$options]);
            return "The file <em>%t</em> is too big. Maximum filesize is {$max_size}.";
        }
        return true;
    }

    /**
     * "is_date" validation function
     *
     * @param  mixed $value the element value
     * @return mixed        TRUE if valid or a string containing the error message
     */
    public static function validateIsDate($value)
    {
        if (!$value || ($value && ($date = date_create($value)) === false)) {
            return "<em>%t</em> is not a valid date.";
        }
        return true;
    }

    /**
     * "email" validation function
     *
     * @param  mixed $value the element value
     * @return mixed        TRUE if valid or a string containing the error message
     */
    public static function validateEmail($value)
    {
        if (empty($value)) {
            return false;
        }
        $check_dns = FORMS_VALIDATE_EMAIL_DNS;
        $blocked_domains = explode('|', FORMS_VALIDATE_EMAIL_BLOCKED_DOMAINS);
        $atIndex = strrpos($value, "@");
        if (is_bool($atIndex) && !$atIndex) {
            return "<em>%t</em> is not a valid email. It must contain the @ symbol.";
        } else {
            $domain = substr($value, $atIndex+1);
            $local = substr($value, 0, $atIndex);
            $localLen = strlen($local);
            $domainLen = strlen($domain);
            if ($localLen < 1 || $localLen > 64) {
                return "<em>%t</em> is not a valid email. Local part is wrong length.";
            } elseif ($domainLen < 1 || $domainLen > 255) {
                return "<em>%t</em> is not a valid email. Domain name is wrong length.";
            } elseif ($local[0] == '.' || $local[$localLen-1] == '.') {
                return "<em>%t</em> is not a valid email. Local part starts or ends with '.'";
            } elseif (preg_match('/\\.\\./', $local)) {
                return "<em>%t</em> is not a valid email. Local part two consecutive dots.";
            } elseif (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
                return "<em>%t</em> is not a valid email. Invalid character in domain.";
            } elseif (preg_match('/\\.\\./', $domain)) {
                return "<em>%t</em> is not a valid email. Domain name has two consecutive dots.";
            } elseif (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\", "", $local))) {
                if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\", "", $local))) {
                    return "<em>%t</em> is not a valid email. Invalid character in local part.";
                }
            }
            if (in_array($domain, $blocked_domains)) {
                return "<em>%t</em> is not a valid email. Domain name is in list of disallowed domains.";
            }
            if ($check_dns && !(checkdnsrr($domain, "MX") || checkdnsrr($domain, "A"))) {
                return "<em>%t</em> is not a valid email. Domain name not found in DNS.";
            }
        }
        return true;
    }

    /**
     * "is_RGB" validation function
     *
     * @param  mixed $value the element value
     * @return mixed        TRUE if valid or a string containing the error message
     */
    public static function validateRgb($value)
    {
        if (!$value || ($value && !preg_match("/^#?([a-f\d]{3}([a-f\d]{3})?)$/i", $value))) {
            return "<em>%t</em> is not a valid RGB color string.";
        }
        return true;
    }

    /**
     * "is_URL" validation function
     *
     * @param  mixed $value the element value
     * @return mixed        TRUE if valid or a string containing the error message
     */
    public static function validateUrl($value)
    {
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

        if (!$value || ($value && !preg_match($URL_FORMAT, $value))) {
            return "<em>%t</em> is not a valid URL string.";
        }
        return true;
    }
}

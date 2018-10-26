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

namespace Degami\PHPFormsApi\Accessories;

use Degami\PHPFormsApi\Abstracts\Base\MultiLevelDataBag;

/**
 * a class to hold form fields submitted values
 */

class SessionBag extends MultiLevelDataBag
{

    /**
     * Class constructor
     *
     * @param mixed $data data to add
     */
    public function __construct($data = [], $parent = null)
    {
        if (!$parent && isset($_SESSION[self::getSessionIdentifier()])) {
            $data = unserialize($_SESSION[self::getSessionIdentifier()]);
        }
        parent::__construct($data, $parent);
    }

    /**
     * {@inheritdoc}
     */
    public function __set($key, $value)
    {
        parent::__set($key, $value);
        $this->notifyChange();
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function __unset($key)
    {
        parent::__unset($key);
        $this->notifyChange();
    }

    /**
     * stores data to session
     */
    public function onChange()
    {
        $_SESSION[self::getSessionIdentifier()] = serialize($this->toArray());
    }

    /**
     * Get session identified
     *
     * @return string
     */
    public static function getSessionIdentifier()
    {
        static $session_identifier = null;
        if (!$session_identifier) {
            if (isset($_SESSION['sessionbag_identifier'])) {
                return $_SESSION['sessionbag_identifier'];
            }
            $session_identifier = 'SESS_'.uniqid();
            $_SESSION['sessionbag_identifier'] = $session_identifier;
        }
        return $session_identifier;
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
     * {@inheritdoc}
     */
    public function clear()
    {
        parent::clear();
        session_destroy();
        session_start();
    }
}

<?php
/**
 * PHP FORMS API bootstrap
 * PHP Version 5.5
 *
 * @category Utils
 * @package  Degami\PHPFormsApi
 * @author   Mirko De Grandis <degami@github.com>
 * @license  MIT https://opensource.org/licenses/mit-license.php
 * @link     https://github.com/degami/php-forms-api
 */

if ((function_exists('session_status') && session_status() != PHP_SESSION_NONE) || trim(session_id()) != '') {
    @ini_set('session.gc_maxlifetime', FORMS_SESSION_TIMEOUT);
    @session_set_cookie_params(FORMS_SESSION_TIMEOUT);
}

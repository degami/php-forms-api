<?php
/**
 * PHP Forms API bootstrap
 */

if (!defined('PHP_FORMS_API_BOOTSTRAPPED')) {
    if ((function_exists('session_status') && session_status() != PHP_SESSION_NONE) || trim(session_id()) != '') {
        @ini_set('session.gc_maxlifetime', FORMS_SESSION_TIMEOUT);
        @session_set_cookie_params(FORMS_SESSION_TIMEOUT);
    }

    define('PHP_FORMS_API_BOOTSTRAPPED', 1);
}

<?php
/**
 * PHP FORMS API defines
 * PHP Version 5.5
 *
 * @category Utils
 * @package  Degami\PHPFormsApi
 * @author   Mirko De Grandis <degami@github.com>
 * @license  MIT https://opensource.org/licenses/mit-license.php
 * @link     https://github.com/degami/php-forms-api
 */

/*
 *  Turn on error reporting during development
 */
// error_reporting(E_ALL);
// ini_set('display_errors', TRUE);
// ini_set('display_startup_errors', TRUE);

namespace Degami\PHPFormsApi;

/*
 *  PHP Forms API library configuration
 */

if (!defined('FORMS_DEFAULT_FORM_CONTAINER_TAG')) {
    define('FORMS_DEFAULT_FORM_CONTAINER_TAG', 'div');
}
if (!defined('FORMS_DEFAULT_FORM_CONTAINER_CLASS')) {
    define('FORMS_DEFAULT_FORM_CONTAINER_CLASS', 'form-container');
}
if (!defined('FORMS_DEFAULT_FIELD_CONTAINER_TAG')) {
    define('FORMS_DEFAULT_FIELD_CONTAINER_TAG', 'div');
}
if (!defined('FORMS_DEFAULT_FIELD_CONTAINER_CLASS')) {
    define('FORMS_DEFAULT_FIELD_CONTAINER_CLASS', 'form-item');
}
if (!defined('FORMS_DEFAULT_FIELD_LABEL_CLASS')) {
    define('FORMS_DEFAULT_FIELD_LABEL_CLASS', '');
}
if (!defined('FORMS_FIELD_ADDITIONAL_CLASS')) {
    define('FORMS_FIELD_ADDITIONAL_CLASS', '');
}
if (!defined('FORMS_VALIDATE_EMAIL_DNS')) {
    define('FORMS_VALIDATE_EMAIL_DNS', true);
}
if (!defined('FORMS_VALIDATE_EMAIL_BLOCKED_DOMAINS')) {
    define('FORMS_VALIDATE_EMAIL_BLOCKED_DOMAINS', 'mailinator.com|guerrillamail.com');
}
if (!defined('FORMS_BASE_PATH')) {
    define('FORMS_BASE_PATH', '');
}
if (!defined('FORMS_XSS_ALLOWED_TAGS')) {
    define('FORMS_XSS_ALLOWED_TAGS', 'a|em|strong|cite|code|ul|ol|li|dl|dt|dd');
}
if (!defined('FORMS_SESSION_TIMEOUT')) {
    define('FORMS_SESSION_TIMEOUT', 7200);
}
if (!defined('FORMS_ERRORS_ICON')) {
    define(
        'FORMS_ERRORS_ICON',
        '<span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>'
    );
}
if (!defined('FORMS_ERRORS_TEMPLATE')) {
    define(
        'FORMS_ERRORS_TEMPLATE',
        '<div class="ui-state-error ui-corner-all errorsbox">' . FORMS_ERRORS_ICON . '<ul>%s</ul></div>'
    );
}
if (!defined('FORMS_HIGHLIGHTS_ICON')) {
    define(
        'FORMS_HIGHLIGHTS_ICON',
        '<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>'
    );
}
if (!defined('FORMS_HIGHLIGHTS_TEMPLATE')) {
    define(
        'FORMS_HIGHLIGHTS_TEMPLATE',
        '<div class="ui-state-highlight ui-corner-all highlightsbox">' . FORMS_HIGHLIGHTS_ICON . '<ul>%s</ul></div>'
    );
}

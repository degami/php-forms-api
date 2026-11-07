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
   ####                    FIELDS                       ####
   ######################################################### */

namespace Degami\PHPFormsApi\Fields;

use Degami\PHPFormsApi\Abstracts\Fields\Wysiwyg;

/**
 * The hugerte input field class
 */
class Hugerte extends Wysiwyg
{
    protected string $wysiwyg_lib_path = "/js/hugerte/hugerte.min.js";

    public function getWysiwygType(): string
    {
        return "hugerte";
    }

    public function getWysiwygJsLibPath(): string
    {
        return $this->wysiwyg_lib_path;
    }

    public function setWysiwygJsLibPath(string $path): Hugerte
    {
        $this->wysiwyg_lib_path = $path;

        return $this;
    }
}

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
 * The tinymce input field class
 */
class Tinymce extends Wysiwyg
{
    protected string $wysiwyg_lib_path = "/js/tinymce/tinymce.min.js";

    public function getWysiwygType(): string
    {
        return "tinymce";
    }

    public function getWysiwygJsLibPath(): string
    {
        return $this->wysiwyg_lib_path;
    }

    public function setWysiwygJsLibPath(string $path): Tinymce
    {
        $this->wysiwyg_lib_path = $path;

        return $this;
    }
}

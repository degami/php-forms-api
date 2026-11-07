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

namespace Degami\PHPFormsApi\Abstracts\Fields;

use Degami\PHPFormsApi\Form;
use Degami\PHPFormsApi\Fields\Textarea;
use \stdClass;

/**
 * wysiwyg beautified textarea
 */
abstract class Wysiwyg extends Textarea
{
    protected string $wysiwyg_lib_path = "";

    /**
     * wysiwyg options
     *
     * @var array
     */
    protected array $wysiwyg_options = FORMS_DEFAULT_WYSIWYG_OPTIONS;

    abstract public function getWysiwygType(): string;

    abstract public function getWysiwygJsLibPath(): string;

    /**
     * Get wysiwyg options array
     *
     * @return array wysiwyg options
     */
    public function &getWysiwygOptions(): array
    {
        return $this->wysiwyg_options;
    }

    /**
     * Set wysiwyg options array
     *
     * @param array $options array of valid wysiwyg options
     * @return self
     */
    public function setWysiwygOptions(array $options): Wysiwyg
    {
        $options = array_filter($options, [$this, 'isValidWysiwygOption']);
        $this->wysiwyg_options = $options;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param Form $form form object
     */
    public function preRender(Form $form) : void
    {
        if ($this->pre_rendered == true) {
            return;
        }
        $id = $this->getHtmlId();
        $this->wysiwyg_options['selector'] = "#{$id}";
        $wysiwyg_options = new stdClass;
        foreach ($this->wysiwyg_options as $key => $value) {
            if (! $this->isValidWysiwygOption($key)) {
                continue;
            }
            $wysiwyg_options->{$key} = $value;
        }

        $wysiwyg_type = $this->getWysiwygType();
        $this->addJs("{$wysiwyg_type}.init(".json_encode($wysiwyg_options).");");
        $this->addJs("
            document.querySelector('form').addEventListener('submit', function() {
                const editor = {$wysiwyg_type}.get('$id');
                const content = editor.getBody().innerHTML;
                const textarea = document.querySelector('textarea#$id');
                if (textarea) {
                    textarea.value = content;
                }
            });
        ");

        $this->addJsFile($this->getWysiwygJsLibPath());

        parent::preRender($form);
    }

    /**
     * filters valid wysiwyg options
     *
     * @param string $propertyname property name
     * @return boolean TRUE if is a valid wysiwyg option
     */
    private function isValidWysiwygOption(string $propertyname): bool
    {
        // could be used to filter elements
        return true;
    }
}

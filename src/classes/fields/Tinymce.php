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
   ####                    FIELDS                       ####
   ######################################################### */

namespace Degami\PHPFormsApi\Fields;

use Degami\PHPFormsApi\Form;
use \stdClass;

/**
 * tinymce beautified textarea
 */
class Tinymce extends Textarea
{
    /**
     * tinymce options
     *
     * @var array
     */
    protected $tinymce_options = [];

    /**
     * Get tinymce options array
     *
     * @return array tinymce options
     */
    public function &getTinymceOptions(): array
    {
        return $this->tinymce_options;
    }

    /**
     * Set tinymce options array
     *
     * @param array $options array of valid tinymce options
     * @return self
     */
    public function setTinymceOptions(array $options): Tinymce
    {
        $options = array_filter($options, [$this, 'isValidTinymceOption']);
        $this->tinymce_options = $options;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param Form $form form object
     */
    public function preRender(Form $form)
    {
        if ($this->pre_rendered == true) {
            return;
        }
        $id = $this->getHtmlId();
        $this->tinymce_options['selector'] = "#{$id}";
        $tinymce_options = new stdClass;
        foreach ($this->tinymce_options as $key => $value) {
            if (! $this->isValidTinymceOption($key)) {
                continue;
            }
            $tinymce_options->{$key} = $value;
        }
        $this->addJs("tinymce.init(".json_encode($tinymce_options).");");
        $this->addJs("
            document.querySelector('form').addEventListener('submit', function() {
                const editor = tinymce.get('$id');
                const content = editor.getBody().innerHTML;
                const textarea = document.querySelector('textarea#$id');
                if (textarea) {
                    textarea.value = content;
                }
            });
        ");
        parent::preRender($form);
    }

    /**
     * filters valid tinymce options
     *
     * @param string $propertyname property name
     * @return boolean TRUE if is a valid tinymce option
     */
    private function isValidTinymceOption(string $propertyname): bool
    {
        // could be used to filter elements
        return true;
    }
}

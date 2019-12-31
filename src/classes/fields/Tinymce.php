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
    public function &getTinymceOptions()
    {
        return $this->tinymce_options;
    }

    /**
     * Set tinymce options array
     *
     * @param array $options array of valid tinymce options
     */
    public function setTinymceOptions($options)
    {
        $options = (array) $options;
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
        parent::preRender($form);
    }

    /**
     * filters valid tinymce options
     *
     * @param  string $propertyname property name
     * @return boolean TRUE if is a valid tinymce option
     */
    private function isValidTinymceOption($propertyname)
    {
        // could be used to filter elements
        return true;
    }
}

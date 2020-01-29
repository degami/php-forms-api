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
use Degami\PHPFormsApi\Abstracts\Base\Field;
use Degami\Basics\Html\TagElement;

/**
 * The color input field class
 */
class Color extends Field
{
    /**
     * Class constructor
     *
     * @param array  $options build options
     * @param string $name    field name
     */
    public function __construct($options = [], $name = null)
    {
        parent::__construct($options, $name);
        if (!empty($this->default_value) && !$this->isRGB($this->default_value)) {
            $this->value = $this->default_value = '#000000';
        }
    }

    /**
     * Check if string is an RGB representation
     *
     * @param  string $str string to check
     * @return boolean     true if string is RGB
     */
    private function isRGB($str)
    {
        return preg_match("/^#?([a-f\d]{3}([a-f\d]{3})?)$/i", $str);
    }

    /**
     * {@inheritdoc}
     *
     * @param Form $form form object
     *
     * @return string        the element html
     */
    public function renderField(Form $form)
    {
        $id = $this->getHtmlId();

        if (!isset($this->attributes['class'])) {
            $this->attributes['class'] = '';
        }
        if ($this->hasErrors()) {
            $this->attributes['class'] .= ' has-errors';
        }
        if ($this->disabled == true) {
            $this->attributes['disabled']='disabled';
        }
        if (is_array($this->value)) {
            $this->value = '';
        }

        $tag = new TagElement([
            'tag' => 'input',
            'type' => 'color',
            'id' => $id,
            'name' => $this->name,
            'value' => htmlspecialchars($this->value),
            'attributes' => $this->attributes + ['size' => $this->size],
        ]);
        return $tag;
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean this is a value
     */
    public function isAValue()
    {
        return true;
    }
}

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

use Degami\Basics\Html\BaseElement;
use Degami\PHPFormsApi\Form;
use Degami\PHPFormsApi\Abstracts\Base\Field;
use Degami\Basics\Html\TagElement;

/**
 * The text input field class
 */
class Textfield extends Field
{
    /**
     * Element maxlenght
     *
     * @var integer
     */
    protected $maxlength = null;

    /**
     * Element minlength
     *
     * @var integer
     */
    protected $minlength = null;

    /**
     * {@inheritdoc}
     *
     * @param Form $form form object
     *
     * @return string|BaseElement        the element html
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

        return new TagElement([
            'tag' => 'input',
            'type' => 'text',
            'id' => $id,
            'name' => $this->name,
            'value' => htmlspecialchars((string) $this->getValues()),
            'attributes' => $this->attributes + ['size' => $this->size],
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean this is a value
     */
    public function isAValue() : bool
    {
        return true;
    }
}

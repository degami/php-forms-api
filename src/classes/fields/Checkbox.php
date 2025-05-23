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
 * The single checkbox input field class
 */
class Checkbox extends Field
{
    /**
     * @var string where (after or before) to print text
     */
    protected $text_position = 'after';

    /**
     * Class constructor
     *
     * @param array  $options build options
     * @param ?string $name    field name
     */
    public function __construct(array $options = [], ?string $name = null)
    {
        parent::__construct($options, $name);
        $this->value = null;
        if (isset($options['value'])) {
            $this->value = $options['value'];
        }
    }

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

        if ($this->disabled == true) {
            $this->attributes['disabled']='disabled';
        }

        $this->label_class .= " label-" . $this->getElementClassName();
        $this->label_class = trim($this->label_class);

        if ($this->value == $this->default_value) {
            $this->attributes['checked'] = 'checked';
        }

        $tag = new TagElement([
            'tag' => 'label',
            'attributes' => ['for' => $id, 'class' => $this->label_class],
            'text' => (($this->text_position == 'before') ? $this->getText($this->title) : ''),
        ]);
        $tag->addChild(new TagElement([
            'tag' => 'input',
            'type' => 'checkbox',
            'id' => $id,
            'name' => $this->name,
            'value' => $this->default_value,
            'attributes' => $this->attributes,
            'text' => (($this->text_position != 'before') ? $this->getText($this->title) : ''),
        ]));
        return $tag;
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean this is a value
     */
    public function isAValue(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed field value
     */
    public function getValues()
    {
        return $this->getValue();
    }
}

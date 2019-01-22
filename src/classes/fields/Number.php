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
use Degami\PHPFormsApi\Accessories\TagElement;

/**
 * The number input field class
 */
class Number extends Field
{
    /**
     * minimum value
     *
     * @var null
     */
    protected $min = null;

    /**
     * maximum value
     *
     * @var null
     */
    protected $max = null;

    /**
     * step value
     *
     * @var integer
     */
    protected $step = 1;

    /**
     * Class constructor
     *
     * @param array  $options build options
     * @param string $name    field name
     */
    public function __construct($options = [], $name = null)
    {
        parent::__construct($options, $name);

        // ensure is numeric validator is present
        $this->getValidate()->addElement('numeric');
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

        $this->attributes['size'] = $this->size;
        if (is_numeric($this->min) && is_numeric($this->max) && $this->max >= $this->min) {
            $this->attributes += [
                'size' => $this->size,
                'min' => $this->min,
                'max' => $this->max,
                'step' => $this->step
            ];
        }

        $tag = new TagElement([
            'tag' => 'input',
            'type' => 'number',
            'id' => $id,
            'name' => $this->name,
            'value' => $this->value,
            'attributes' => $this->attributes,
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

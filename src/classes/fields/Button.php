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
use Degami\Basics\Html\TagElement;
use Degami\PHPFormsApi\Abstracts\Fields\Clickable;

/**
 * The button field class
 */
class Button extends Clickable
{

    /**
     * Element label
     *
     * @var string
     */
    protected $label;

    /**
     * Class constructor
     *
     * @param array  $options build options
     * @param ?string $name    field name
     */
    public function __construct(array $options = [], ?string $name = null)
    {
        parent::__construct($options, $name);
        if (empty($this->label)) {
            $this->label = $this->getValues();
        }
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
        if ($this->disabled == true) {
            $this->attributes['disabled']='disabled';
        }

        return new TagElement([
            'tag' => 'button',
            'id' => $id,
            'name' => $this->name,
            'value' => $this->getValues(),
            'text' => $this->getText($this->label),
            'attributes' => $this->attributes,
            'has_close' => true,
        ]);
    }
}

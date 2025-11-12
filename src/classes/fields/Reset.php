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

use Degami\Basics\Html\BaseElement;
use Degami\PHPFormsApi\Form;
use Degami\Basics\Html\TagElement;
use Degami\PHPFormsApi\Abstracts\Fields\Action;

/**
 * The reset button field class
 */
class Reset extends Action
{

    /**
     * Class constructor
     *
     * @param array  $options build options
     * @param ?string $name    field name
     */
    public function __construct(array $options = [], ?string $name = null)
    {
        parent::__construct($options, $name);
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
        if (empty($this->value)) {
            $this->value = 'Reset';
        }
        if ($this->disabled == true) {
            $this->attributes['disabled']='disabled';
        }

        return new TagElement([
            'tag' => 'input',
            'type' => 'reset',
            'id' => $id,
            'name' => $this->name,
            'value' => $this->getText($this->getValues()),
            'attributes' => $this->attributes,
        ]);
    }
}

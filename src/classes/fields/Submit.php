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
use Degami\Basics\Html\TagElement;
use Degami\PHPFormsApi\Abstracts\Fields\Clickable;

/**
 * The submit input type field class
 */
class Submit extends Clickable
{
    /**
     * {@inheritdoc}
     *
     * @param Form $form form object
     * @return string|BaseElement        the element html
     */
    public function renderField(Form $form)
    {
        $id = $this->getHtmlId();
        if (empty($this->value)) {
            $this->value = 'Submit';
        }
        if ($this->disabled == true) {
            $this->attributes['disabled']='disabled';
        }

        $tag = new TagElement([
            'tag' => 'input',
            'type' => 'submit',
            'id' => $id,
            'name' => $this->name,
            'value' => $this->getText($this->getValues()),
            'attributes' => $this->attributes,
        ]);
        return $tag;
    }
}

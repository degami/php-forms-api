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
use Degami\PHPFormsApi\Accessories\TagElement;

/**
 * The range input field class
 */
class Range extends Number
{

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
            'type' => 'range',
            'id' => $id,
            'name' => $this->name,
            'value' => $this->value,
            'attributes' => $this->attributes,
        ]);
        return $tag;
    }
}

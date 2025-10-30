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
   ####              FIELD CONTAINERS                   ####
   ######################################################### */

namespace Degami\PHPFormsApi\Containers;

use Degami\PHPFormsApi\Abstracts\Base\Field;
use Degami\PHPFormsApi\Form;
use Degami\PHPFormsApi\Abstracts\Base\FieldsContainer;
use Degami\PHPFormsApi\Fields\Hidden;

/**
 * an hidden field container
 */
class SeamlessContainer extends FieldsContainer
{

    /**
     * {@inheritdoc}
     *
     * @param Form $form form object
     *
     * @return string        the element html
     */
    public function renderField(Form $form): string
    {
        $output = "";

        $insertorder = array_flip($this->insert_field_order);
        $weights = [];
        $order = [];
        foreach ($this->getFields() as $key => $elem) {
            /** @var Field $elem */
            $weights[$key]  = $elem->getWeight();
            $order[$key] = isset($insertorder[$key]) ? $insertorder[$key] : PHP_INT_MAX;
        }
        if (count($this->getFields()) > 0) {
            array_multisort($weights, SORT_ASC, $order, SORT_ASC, $this->getFields());
        }

        // hidden fields are always first
        usort($this->getFields(), function($fieldA, $fieldB){
            if (is_object($fieldA) && is_a($fieldA, Hidden::class)) {
                return -1;
            }
            if (is_object($fieldB) && is_a($fieldB, Hidden::class)) {
                return 1;
            }
            return 0;
        });

        foreach ($this->getFields() as $name => $field) {
            /** @var Field $field */
            $output .= $field->renderHTML($form);
        }

        return $output;
    }
}

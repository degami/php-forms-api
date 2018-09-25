<?php
/**
 * PHP FORMS API
 *
 * @package degami/php-forms-api
 */
/* #########################################################
   ####              FIELD CONTAINERS                   ####
   ######################################################### */

namespace Degami\PHPFormsApi\Containers;

use Degami\PHPFormsApi\Form;
use Degami\PHPFormsApi\Abstracts\Base\FieldsContainer;

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
    public function renderField(Form $form)
    {
        $output = "";

        $insertorder = array_flip($this->insert_field_order);
        $weights = [];
        $order = [];
        foreach ($this->getFields() as $key => $elem) {
            /** @var \Degami\PHPFormsApi\Abstracts\Base\Field $elem */
            $weights[$key]  = $elem->getWeight();
            $order[$key] = $insertorder[$key];
        }
        if (count($this->getFields()) > 0) {
            array_multisort($weights, SORT_ASC, $order, SORT_ASC, $this->getFields());
        }
        foreach ($this->getFields() as $name => $field) {
            /** @var \Degami\PHPFormsApi\Abstracts\Base\Field $field */
            $output .= $field->render($form);
        }

        return $output;
    }
}

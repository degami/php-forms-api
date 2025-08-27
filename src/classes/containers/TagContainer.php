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
use Degami\Basics\Html\TagElement;
use Degami\PHPFormsApi\Fields\Hidden;

/**
 * a field container that can specify container's html tag
 */
class TagContainer extends FieldsContainer
{
    /**
     * container html tag
     *
     * @var string
     */
    protected $tag = 'div';

    /**
     * Class constructor
     *
     * @param array  $options build options
     * @param string $name    field name
     */
    public function __construct($options = [], $name = null)
    {
        parent::__construct($options, $name);

        if ($this->attributes['class'] == 'tag_container') { // if set to the default
            $this->attributes['class'] = $this->tag.'_container';
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param Form $form form object
     *
     * @return string|TagElement        the element html
     */
    public function renderField(Form $form)
    {
        $id = $this->getHtmlId();

        $tag = new TagElement([
            'tag' => $this->tag,
            'id' => $id,
            'attributes' => $this->attributes,
            'has_close' => true,
            'value_needed' => false,
        ]);

        $insertorder = array_flip($this->insert_field_order);
        $weights = [];
        $order = [];
        foreach ($this->getFields() as $key => $elem) {
            /** @var Field $elem */
            $weights[$key]  = $elem->getWeight();
            $order[$key] = $insertorder[$key] ?? PHP_INT_MAX;
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
            $tag->addChild($field->renderHTML($form));
        }
        return $tag;
    }
}

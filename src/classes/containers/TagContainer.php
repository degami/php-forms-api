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

use Degami\PHPFormsApi\Form;
use Degami\PHPFormsApi\Abstracts\Base\FieldsContainer;
use Degami\Basics\Html\TagElement;

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
     * @return string        the element html
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
            /** @var \Degami\PHPFormsApi\Abstracts\Base\Field $elem */
            $weights[$key]  = $elem->getWeight();
            $order[$key] = $insertorder[$key];
        }
        if (count($this->getFields()) > 0) {
            array_multisort($weights, SORT_ASC, $order, SORT_ASC, $this->getFields());
        }
        foreach ($this->getFields() as $name => $field) {
            /** @var \Degami\PHPFormsApi\Abstracts\Base\Field $field */
            $tag->addChild($field->renderHTML($form));
        }
        return $tag;
    }
}

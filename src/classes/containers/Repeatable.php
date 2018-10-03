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
use Degami\PHPFormsApi\Abstracts\Containers\FieldsContainerMultiple;
use Degami\PHPFormsApi\Accessories\TagElement;
use \Exception;

/**
 * a field container with a repeatable group of fields
 */
class Repeatable extends FieldsContainerMultiple
{
    /** @var integer number of initial repetitions */
    protected $num_reps = 1;

    /** @var array fields to repeat */
    private $repetable_fields = [];

    /** @var array field order */
    private $repetable_insert_field_order = [];

    /**
     * {@inheritdocs}
     *
     * @param array  $options options
     * @param string|null $name field name
     */
    public function __construct(array $options = [], $name = null)
    {
        parent::__construct($options, $name);
    }

    /**
     * Override add_field
     *
     * @param string $name
     * @param mixed  $field
     * @param integer $partitions_index
     *
     * @return $this|mixed
     * @throws \Exception
     */
    public function addField($name, $field, $partitions_index = 0)
    {
        $field = $this->getFieldObj($name, $field);

        if ($this->isFieldContainer($field)) {
            throw new Exception('Can\'t nest field_containers into repeteables');
        }

        $this->repetable_fields[$name] = $field;
        $this->repetable_insert_field_order[] = $name;

        if (!method_exists($field, 'onAddReturn')) {
            if ($this->isFieldContainer($field)) {
                return $field;
            }
            return $this;
        }
        if ($field->onAddReturn() == 'this') {
            return $field;
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param $name
     * @param integer $partitions_index unused
     *
     * @return Repatable
     */
    public function removeField($name, $partitions_index = 0)
    {
        unset($this->repetable_fields[$name]);
        if (($key = array_search($name, $this->repetable_insert_field_order)) !== false) {
            unset($this->repetable_insert_field_order[$key]);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param array $request
     *
     * @throws \Exception
     */
    public function alterRequest(&$request)
    {
        $id = $this->getHtmlId();
        if (isset($request[ $id.'-numreps' ])) {
            $this->num_reps = (int) $request[ $id.'-numreps' ];
            if ($this->num_reps <= 0) {
                $this->num_reps = 1;
            }
        }
        for ($i = 0; $i < $this->num_reps; $i++) {
            foreach ($this->repetable_fields as $rfield) {
                /**
                 * @var \Degami\PHPFormsApi\Abstracts\Base\Field $field
                 */
                $field = clone $rfield;
                $field
                    ->setId($this->getName().'_'.$i.'_'.$field->getName())
                    ->setName($this->getName().'['.$i.']['.$field->getName().']');
                parent::addField($field->getName(), $field, $i);
            }
        }
        parent::alterRequest($request);
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $values
     */
    public function processValue($values)
    {
        $valuestoprocess = array_values($values[ $this->getName() ]);

        foreach ($this->getFields() as $i => $field) {
            /**
             * @var \Degami\PHPFormsApi\Abstracts\Base\Field $field
             */
            $matches = null;
            if (preg_match("/".$this->getName()."\[([0-9]+)\]\[(.*?)\]/", $field->getName(), $matches)) {
                if (isset($valuestoprocess[ $matches[1] ][ $matches[2] ])) {
                    $field->processValue($valuestoprocess[ $matches[1] ][ $matches[2] ]);
                }
            }
        }
        //parent::processValue($values);
    }

    /**
     * {@inheritdoc}
     *
     * @return array|mixed
     */
    public function getValue()
    {
        $out = [];
        foreach ($this->getFields() as $i => $field) {
            /**
             * @var \Degami\PHPFormsApi\Abstracts\Base\Field $field
             */
            if ($field->isAValue() == true) {
                $matches = null;
                if (preg_match("/".$this->getName()."\[([0-9]+)\]\[(.*?)\]/", $field->getName(), $matches)) {
                    $out[ $matches[1] ][ $matches[2] ] = $field->getValue();
                }
            }
        }
        return $out;
    }

    /**
     * {@inheritdoc}
     *
     * @return array|mixed
     */
    public function getValues()
    {
        return $this->getValue();
    }


    /**
     * {@inheritdoc}
     *
     * @param \Degami\PHPFormsApi\Form $form
     */
    public function preRender(Form $form)
    {
        if (!$this->pre_rendered) {
            $id = $this->getHtmlId();

            $repetatable_fields = "<div id=\"{$id}-row-{x}\">\n<div class=\"repeatable-row\">";
            $fake_form = new Form();
            foreach ($this->repetable_fields as $rfield) {
                /**
                 * @var \Degami\PHPFormsApi\Abstracts\Base\Field $field
                 */
                $field = clone $rfield;
                $field
                    ->setId($this->getName().'_{x}_'.$field->getName())
                    ->setName($this->getName().'[{x}]['.$field->getName().']');
                $repetatable_fields .= $field->renderHTML($fake_form);
            }
            $repetatable_fields .= "<a href=\"#\" class=\"remove-btn btn\" name=\"{$id}-remove-{x}\">&times;</a>\n";
            $repetatable_fields .= "</div></div>";
            $repetatable_fields = str_replace("\n", "", $repetatable_fields);

            $js = array_filter(array_map('trim', $fake_form->getJs()));
            if (!empty($js)) {
                foreach ($js as &$js_string) {
                    if ($js_string[strlen($js_string)-1] == ';') {
                        $js_string = substr($js_string, 0, strlen($js_string)-1);
                    }
                }
            }
            if (!empty($js)) {
                $js = "eval( ".implode(";", $js).".replace( new RegExp('\{x\}', 'g'), newrownum )  );\n";
            } else {
                $js = '';
            }

            $this->addCss("#{$id} .repeatable-row{ 
                margin: 10px 0; 
                padding: 10px; 
                border: solid 1px #cecece; 
                position: relative; 
            }");
            $this->addCss("#{$id} .repeatable-row .remove-btn{
                position: absolute; 
                top: 5px; 
                right: 10px; 
                z-index: 10;
            }");

            $this->addJs("\$('#{$id}').delegate('.remove-btn','click',function(evt){
                evt.preventDefault();
                \$(this).closest('.repeatable-row').remove();
                var \$target = $('.fields-target:eq(0)');
                var newrownum = \$target.find('.repeatable-row').length;
                \$('input[name=\"{$id}-numreps\"]').val(newrownum);
              });");
            $this->addJs("\$('.btnaddmore', '#{$id}').click(function(evt){
                evt.preventDefault();
                var \$target = \$('.fields-target:eq(0)');
                var newrownum = \$target.find('.repeatable-row').length;
                \$( '{$repetatable_fields}'.replace( new RegExp('\{x\}', 'g'), newrownum ) ).appendTo( \$target );
                \$('input[name=\"{$id}-numreps\"]').val(newrownum);
                {$js}
              });");
        }

        return parent::preRender($form);
    }


    /**
     * {@inheritdocs}
     *
     * @param Form $form form object
     * @return string|tag_element the field html
     */
    public function renderField(Form $form)
    {
        $id = $this->getHtmlId();

        $tag = new TagElement(
            [
                'tag' => 'div',
                'id' => $id,
                'attributes' => $this->attributes,
            ]
        );

        $target = new TagElement(
            [
                'tag' => 'div',
                'attributes' => ['class' => 'fields-target'],
            ]
        );

        $tag->addChild($target);

        foreach ($this->partitions as $partitionindex => $tab) {
            $insertorder = array_flip($this->insert_field_order[$partitionindex]);
            $weights = [];
            $order = [];

            $partition_fields = $this->getPartitionFields($partitionindex);

            foreach ($partition_fields as $key => $elem) {
                /** @var \Degami\PHPFormsApi\Abstracts\Base\Field $elem */
                $weights[$key]  = $elem->getWeight();
                $order[$key] = $insertorder[$key];
            }
            if (count($partition_fields) > 0) {
                array_multisort($weights, SORT_ASC, $order, SORT_ASC, $partition_fields);
            }

            $inner = new TagElement(
                [
                    'tag' => 'div',
                    'id' => $id.'-row-'.$partitionindex,
                ]
            );
            $target->addChild($inner);

            $repeatablerow = new TagElement(
                [
                    'tag' => 'div',
                    'attributes' => ['class' => 'repeatable-row'],
                ]
            );
            $inner->addChild($repeatablerow);

            $repeatablerow->addChild("<input type=\"hidden\" name=\"{$id}-numreps\" value=\"{$this->num_reps}\" />\n");
            foreach ($partition_fields as $name => $field) {
                /** @var \Degami\PHPFormsApi\Abstracts\Base\Field $field */
                $repeatablerow->addChild($field->renderHTML($form));
            }
            $repeatablerow->addChild("<a href=\"#\"
                                            class=\"remove-btn btn\" 
                                            name=\"{$id}-remove-{$partitionindex}\">&times;</a>\n");
        }

        $tag->addChild(
            new TagElement(
                [
                    'tag' => 'button',
                    'id' => $id.'-btn-addmore',
                    'attributes' => ['class' => 'btn btnaddmore'],
                    'text' => $this->getText('+'),
                    'has_close' => true,
                    'value_needed' => false,
                ]
            )
        );

        return $tag;
    }
}

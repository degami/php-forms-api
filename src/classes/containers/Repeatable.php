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
use Degami\PHPFormsApi\Abstracts\Containers\FieldsContainerMultiple;
use Degami\Basics\Html\TagElement;
use Degami\PHPFormsApi\Exceptions\FormException;

/**
 * a field container with a repeatable group of fields
 */
class Repeatable extends FieldsContainerMultiple
{
    /** @var integer number of initial repetitions */
    protected $num_reps = null;

    /** @var array fields to repeat */
    private $repetable_fields = [];

    /** @var array field order */
    private $repetable_insert_field_order = [];

    /** @var array default values */
    protected $default_value = [];

    /**
     * {@inheritdocs}
     *
     * @param array       $options options
     * @param string|null $name    field name
     */
    public function __construct(array $options = [], $name = null)
    {
        parent::__construct($options, $name);
        if (is_array($options['default_value'])) {
            $this->default_value = $options['default_value'];
            $this->num_reps = count($this->default_value);
        }
    }

    /**
     * Override add_field
     *
     * @param string  $name
     * @param mixed   $field
     * @param integer $partitions_index
     *
     * @return $this|mixed
     * @throws FormException
     */
    public function addField($name, $field, $partitions_index = 0)
    {
        $field = $this->getFieldObj($name, $field);

        if ($this->isFieldContainer($field)) {
            throw new FormException('Can\'t nest field_containers into repeteables');
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
     * @throws FormException
     */
    public function alterRequest(&$request)
    {
        $id = $this->getHtmlId();
        if (isset($request[ $id.'-numreps' ])) {
            $this->num_reps = (int) $request[ $id.'-numreps' ];
            if ($this->num_reps < 0) {
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

                if (isset($this->default_value[$i][$rfield->getName()])) {
                    $field->setValue($this->default_value[$i][$rfield->getName()]);
                }
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
        foreach ($this->getFields() as $i => $field) {
            /**
             * @var \Degami\PHPFormsApi\Abstracts\Base\Field $field
             */
            $field->processValue(static::traverseArray($values, $field->getName()));
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
                $key = str_replace($this->getName(), "", $field->getName());
                if (preg_match('/\[([0-9]+)\]\[(.*?)\]/i', $key, $matches)) {
                    $out[$matches[1]][$matches[2]] = $field->getValue();
                } else {
                    $out[$field->getName()] = $field->getValue();
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
     * {@inheritdocs}
     */
    public function isValid()
    {
        if ($this->num_reps == 0) {
            return true;
        }
        return parent::isValid();
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
                    ->setId($this->getHtmlId().'_{x}_'.$field->getName())
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

            $this->addCss(
                "#{$id} .repeatable-row{
                margin: 10px 0;
                padding: 10px;
                border: solid 1px #cecece;
                position: relative;
            }"
            );
            $this->addCss(
                "#{$id} .repeatable-row .remove-btn{
                position: absolute;
                top: 5px;
                right: 10px;
                z-index: 10;
            }"
            );

            $this->addJs(
                "\$('#{$id}').delegate('.remove-btn','click',function(evt){
                evt.preventDefault();
                \$(this).closest('.repeatable-row').remove();
                var \$target = $('.fields-target:eq(0)');
                var newrownum = \$target.find('.repeatable-row').length;
                \$('input[name=\"{$id}-numreps\"]').val(newrownum);
              });"
            );
            $this->addJs(
                "\$('.btnaddmore', '#{$id}').click(function(evt){
                evt.preventDefault();
                var \$target = \$('.fields-target:eq(0)');
                var newrownum = \$target.find('.repeatable-row').length;
                \$( '{$repetatable_fields}'.replace( new RegExp('\{x\}', 'g'), newrownum ) ).appendTo( \$target );
                \$('input[name=\"{$id}-numreps\"]').val(newrownum + 1);
                {$js}
              });"
            );
        }

        return parent::preRender($form);
    }


    /**
     * {@inheritdocs}
     *
     * @param  Form $form form object
     * @return string|tag_element the field html
     */
    public function renderField(Form $form)
    {
        $id = $this->getHtmlId();

        $tag = new TagElement([
            'tag' => 'div',
            'id' => $id,
            'attributes' => $this->attributes,
            'text' => $this->getTitle(),
        ]);

        $target = new TagElement([
            'tag' => 'div',
            'attributes' => ['class' => 'fields-target'],
        ]);

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

            $inner = new TagElement([
                'tag' => 'div',
                'id' => $id.'-row-'.$partitionindex,
            ]);
            $target->addChild($inner);

            $repeatablerow = new TagElement([
                'tag' => 'div',
                'attributes' => ['class' => 'repeatable-row'],
            ]);
            $inner->addChild($repeatablerow);

            foreach ($partition_fields as $name => $field) {
                /** @var \Degami\PHPFormsApi\Abstracts\Base\Field $field */
                $repeatablerow->addChild($field->renderHTML($form));
            }
            $repeatablerow->addChild(
                "<a href=\"#\" class=\"remove-btn btn\" name=\"{$id}-remove-{$partitionindex}\">&times;</a>\n"
            );
        }

        $tag->addChild(new TagElement([
            'tag' => 'input',
            'type' => 'hidden',
            'name' => $id.'-numreps',
            'value' => $this->num_reps,
        ]));
        $tag->addChild(new TagElement([
            'tag' => 'button',
            'id' => $id.'-btn-addmore',
            'attributes' => ['class' => 'btn btnaddmore'],
            'text' => $this->getText('+'),
            'has_close' => true,
            'value_needed' => false,
        ]));

        return $tag;
    }


    /**
     * render the field
     *
     * @param Form $form form object
     *
     * @return string        the field html
     */
    public function renderHTML(Form $form)
    {
        $id = $this->getHtmlId();
        $output = $this->getElementPrefix();
        $output .= $this->getPrefix();

        // this container needs a label
        if (!empty($this->title)) {
            if ($this->tooltip == false) {
                $this->label_class .= " label-" . $this->getElementClassName();
                $this->label_class = trim($this->label_class);
                $label_class = (!empty($this->label_class)) ? " class=\"{$this->label_class}\"" : "";
                $output .= "<label for=\"{$id}\" {$label_class}>".
                            $this->getText($this->title).
                            "</label>\n";
            } else {
                if (!in_array('title', array_keys($this->attributes))) {
                    $this->attributes['title'] = strip_tags($this->getText($this->title));
                }

                $id = $this->getHtmlId();
                $form->addJs("\$('#{$id}','#{$form->getId()}').tooltip();");
            }
        }


        if (!$this->pre_rendered) {
            $this->preRender($form);
            $this->pre_rendered = true;
        }
        $output .= $this->renderField($form);

        if (!($this instanceof FieldsContainer)) {
            if (!empty($this->description)) {
                $output .= "<div class=\"description\">{$this->description}</div>";
            }
        }
        if ($form->errorsInline() == true && $this->hasErrors()) {
            $output.= '<div class="inline-error has-errors">'.implode("<br />", $this->getErrors()).'</div>';
        }

        $output .= $this->getSuffix();
        $output .= $this->getElementSuffix();

        if (count($this->event) > 0 && trim($this->getAjaxUrl()) != '') {
            foreach ($this->event as $event) {
                $eventjs = $this->generateEventJs($event, $form);
                $this->addJs($eventjs);
            }
        }

        // let others alter the output
        static::executeAlter("/.*?_".static::getClassNameString()."_render_output_alter$/i", [&$output]);

        // return html string
        return $output;
    }
}

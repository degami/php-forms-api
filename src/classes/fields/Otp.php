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
use Degami\PHPFormsApi\Containers\SeamlessContainer;

/**
 * The otp input field class
 */
class Otp extends Textfield
{
    // not extending hidden has we do not want to empty container tag and class

    public const TYPE_NUMERIC = 'numeric';
    public const TYPE_ALPHA = 'alpha';
    public const TYPE_ALPHA_NUMERIC = 'alpha_numeric';

    protected $otp_length = 6;

    protected $otp_type = self::TYPE_NUMERIC;

    protected $show_characters = false;

    protected $show_hide = false;

    public function __construct($options = [], ?string $name = null)
    {
        parent::__construct($options, $name);

        // do some checks
        if (isset($options['maxlength'])) {
            $this->otp_length = $options['maxlength'];
        }
        if (isset($options['otp_length'])) {
            $this->minlength = $this->maxlength = $this->otp_length;
        }
        if ($this->show_characters == true)  {
            $this->show_hide = false;
        }
    }

   /**
     * {@inheritdoc}
     *
     * @param Form $form form object
     */
    public function preRender(Form $form)
    {
        if ($this->pre_rendered == true) {
            return;
        }
        $id = $this->getHtmlId();
        $this->addJs("
            $('input', '#{$id}_digits').on('change', function() {
                var value = '';
                for (var i = 0; i < ".$this->otp_length."; i++) {
                    value += $('#{$id}_' + i).val();
                }
                $('#{$id}').val(value);
            });
        ");

        if ($this->show_hide) {
            $this->addJs("
                $('.otp-show-hide', '#{$id}_digits').on('click', function(e) {
                    e.preventDefault();
                    var inputs = $('#{$id}_digits input');
                    if (inputs.attr('type') === 'password') {
                        inputs.attr('type', 'text');
                        $(this).text('".$this->getText('Hide')."');
                    } else {
                        inputs.attr('type', 'password');
                        $(this).text('".$this->getText('Show')."');
                    }
                });            
            ");
        }

        parent::preRender($form);
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

        if (!isset($this->attributes['class'])) {
            $this->attributes['class'] = '';
        }
        if ($this->hasErrors()) {
            $this->attributes['class'] .= ' has-errors';
        }
        if ($this->disabled == true) {
            $this->attributes['disabled']='disabled';
        }
        if (is_array($this->value)) {
            $this->value = '';
        }

        $container = new SeamlessContainer([
            'tag' => 'div',
            'class' => 'otp-container',
        ]);

        $container->addMarkup('<div id="'.$id.'_digits" class="otp-digits" style="display: flex; justify-content: center; align-items: center;">');
        for ($i = 0; $i < $this->otp_length; $i++) {

            $onInput = "";
            // note only one validator is allowed on js side
            $fieldValidators = array_map(fn ($validator) => (is_array($validator) ? $validator['validator'] : $validator), $this->getValidate()->toArray());
            $fieldValidators = array_filter($fieldValidators, fn ($validator) => in_array($validator, ['alpha_numeric', 'alpha', 'numeric', 'integer']));

            if (count($fieldValidators) == 0) {
                switch ($this->otp_type) {
                    case self::TYPE_ALPHA_NUMERIC:
                        $fieldValidators[] = 'alpha_numeric';
                        break;
                    case self::TYPE_ALPHA:
                        $fieldValidators[] = 'alpha';
                        break;
                    case self::TYPE_NUMERIC:
                    default:
                        $fieldValidators[] = 'numeric';
                        break;
                }
            }

            if (count($fieldValidators) && in_array('alpha_numeric', $fieldValidators)) {
                $onInput = "this.value = this.value.replace(/[^a-zA-Z0-9]/g, '');";
            } else if (count($fieldValidators) && in_array('alpha', $fieldValidators)) {
                $onInput = "this.value = this.value.replace(/[^a-zA-Z]/g, '');";
            } else if (count($fieldValidators) && count(array_intersect($fieldValidators, ['numeric', 'integer']))) {
                $onInput = "this.value = this.value.replace(/[^0-9]/g, '');";
            }

            $onInput .= "this.value = this.value.toUpperCase();";
            $onKeyUp = "if (event.key !== 'Tab' && this.value.length === 1) { document.getElementById('" . $id . '_' . ($i + 1) . "').focus(); }";
            if ($i == $this->otp_length - 1) {
                $onKeyUp = "if (event.key !== 'Tab' && this.value.length === 1) { this.blur(); }";
            }

            $container->addMarkup(new TagElement([
                'tag' => 'input',
                'type' => $this->show_characters ? 'text' : 'password',
                'id' => $id . '_' . $i,
                'name' => $this->name . '_chars' . '[' . $i . ']',
                'value' => '',
                'attributes' => [
                    'size' => 1,
                    'maxlength' => 1,
                    'minlength' => 1,
//                    'class' => 'otp-input inline',
                    'autocomplete' => 'off',
                    'oninput' => $onInput,
                    'onkeyup' => $onKeyUp,
                    'style' => "width: auto !important; display: inline-flex; text-align: center;margin-right: 5px;",
                ] + $this->attributes,
            ]));
        }
        if ($this->show_hide) {
            $container->addMarkup(new TagElement([
                'tag' => 'a',
                'href' => '#',
                'attributes' => [
                    'class' => 'otp-show-hide',
                    'style' => 'cursor: pointer; margin-left: 10px;',
                ],
                'text' => $this->getText( $this->show_characters ? 'Hide' : 'Show' ),
            ]));
        }
        $container->addMarkup('</div>');

        $container->addField(
            $this->name, [
                'type' => 'hidden',
                'default_value' => $this->getValues(),
            ]
        );

        return $container->renderField($form);
    }
}
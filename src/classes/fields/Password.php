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
use Degami\PHPFormsApi\Abstracts\Base\Field;
use Degami\PHPFormsApi\Accessories\TagElement;
use Degami\PHPFormsApi\Accessories\TagList;

/**
 * The password input field class
 */
class Password extends Field
{

    /**
     * "with confirmation" flag
     *
     * @var boolean
     */
    protected $with_confirm = false;

    /**
     * confirmation input label
     *
     * @var string
     */
    protected $confirm_string = "Confirm password";

    /**
     * "include javascript strength check" flag
     *
     * @var boolean
     */
    protected $with_strength_check = false;

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
        if ($this->with_strength_check == true) {
            $id = $this->getHtmlId();

            $this->addJs(
                "
      \$('#{$id}','#{$form->getId()}').keyup(function() {
        \$('#{$id}_result').html(

        (function(password){
            var strength = 0;
            if (password.length < 6) {
              \$('#{$id}_result').removeClass().addClass('password_strength_checker').addClass('short');
              return '".$this->getText('Too short')."';
            }

            if (password.length > 7) strength += 1;
            if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/))  strength += 1;
            if (password.match(/([a-zA-Z])/) && password.match(/([0-9])/))  strength += 1;
            if (password.match(/([!,%,&,@,#,$,^,*,?,_,~])/))  strength += 1;
            if (password.match(/(.*[!,%,&,@,#,$,^,*,?,_,~].*[!,%,&,@,#,$,^,*,?,_,~])/)) strength += 1;
            if (strength < 2 ){
              \$('#{$id}_result').removeClass().addClass('password_strength_checker').addClass('weak');
              return '".$this->getText('Weak')."';
            } else if (strength == 2 ) {
              \$('#{$id}_result').removeClass().addClass('password_strength_checker').addClass('good');
              return '".$this->getText('Good')."';
            } else {
              \$('#{$id}_result').removeClass().addClass('password_strength_checker').addClass('strong');
              return '".$this->getText('Strong')."';
            }
          })(\$('#{$id}','#{$form->getId()}').val())

        );
      });"
            );

            $this->addCss("#{$form->getId()} .password_strength_checker.short{color:#FF0000;}");
            $this->addCss("#{$form->getId()} .password_strength_checker.weak{color:#E66C2C;}");
            $this->addCss("#{$form->getId()} .password_strength_checker.good{color:#2D98F3;}");
            $this->addCss("#{$form->getId()} .password_strength_checker.strong{color:#006400;}");
        }

        parent::preRender($form);
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

        if (!isset($this->attributes['class'])) {
            $this->attributes['class'] = '';
        }
        if ($this->hasErrors()) {
            $this->attributes['class'] .= ' has-errors';
        }
        if ($this->disabled == true) {
            $this->attributes['disabled'] = 'disabled';
        }

        $tag = new TagList();
        $tag->addChild(new TagElement([
            'tag' => 'input',
            'type' => 'password',
            'id' => $id,
            'name' => $this->name,
            'value' => "",
            'attributes' => $this->attributes + ['size' => $this->size],
        ]));

        if ($this->with_confirm == true) {
            $tag->addChild(new TagElement([
                'tag' => 'label',
                'attributes' => ['for' => $id.'-confirm'],
                'text' => $this->getText($this->confirm_string),
            ]));
            $tag->addChild(new TagElement([
                'tag' => 'input',
                'type' => 'password',
                'id' => $id.'-confirm',
                'name' => $this->name.'_confirm',
                'value' => "",
                'attributes' => $this->attributes + ['size' => $this->size],
            ]));
        }
        if ($this->with_strength_check) {
            $tag->addChild(new TagElement([
                'tag' => 'span',
                'id' => $id.'_result',
                'attributes' => ['class' => 'password_strength_checker'],
            ]));
        }
        return $tag;
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean check if element is valid
     */
    public function isValid()
    {
        if ($this->with_confirm == true) {
            if (!isset($_REQUEST["{$this->name}_confirm"]) || $_REQUEST["{$this->name}_confirm"] != $this->value) {
                $this->addError($this->getText("The passwords do not match"), __FUNCTION__);

                if ($this->stop_on_first_error) {
                    return false;
                }
            }
        }
        return parent::isValid();
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean this is a value
     */
    public function isAValue()
    {
        return true;
    }
}

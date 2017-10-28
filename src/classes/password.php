<?php
/**
 * PHP FORMS API
 * @package degami/php-forms-api
 */
/* #########################################################
   ####                    FIELDS                       ####
   ######################################################### */

namespace Degami\PHPFormsApi;

/**
 * the password input field class
 */
class password extends field {

  /**
   * "with confirmation" flag
   * @var boolean
   */
  protected $with_confirm = FALSE;

  /**
   * confirmation input label
   * @var string
   */
  protected $confirm_string = "Confirm password";

  /**
   * "include javascript strength check" flag
   * @var boolean
   */
  protected $with_strength_check = FALSE;

  /**
   * pre_render hook
   * @param  form $form form object
   */
  function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    if($this->with_strength_check == TRUE){
      $id = $this->get_html_id();

      $this->add_js("
      \$('#{$id}','#{$form->get_id()}').keyup(function() {
        \$('#{$id}_result').html(

        (function(password){
            var strength = 0;
            if (password.length < 6) {
              \$('#{$id}_result').removeClass().addClass('password_strength_checker').addClass('short');
              return '".$this->get_text('Too short')."';
            }

            if (password.length > 7) strength += 1;
            if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/))  strength += 1;
            if (password.match(/([a-zA-Z])/) && password.match(/([0-9])/))  strength += 1;
            if (password.match(/([!,%,&,@,#,$,^,*,?,_,~])/))  strength += 1;
            if (password.match(/(.*[!,%,&,@,#,$,^,*,?,_,~].*[!,%,&,@,#,$,^,*,?,_,~])/)) strength += 1;
            if (strength < 2 ){
              \$('#{$id}_result').removeClass().addClass('password_strength_checker').addClass('weak');
              return '".$this->get_text('Weak')."';
            } else if (strength == 2 ) {
              \$('#{$id}_result').removeClass().addClass('password_strength_checker').addClass('good');
              return '".$this->get_text('Good')."';
            } else {
              \$('#{$id}_result').removeClass().addClass('password_strength_checker').addClass('strong');
              return '".$this->get_text('Strong')."';
            }
          })(\$('#{$id}','#{$form->get_id()}').val())

        );
      });");

      $this->add_css("#{$form->get_id()} .password_strength_checker.short{color:#FF0000;}");
      $this->add_css("#{$form->get_id()} .password_strength_checker.weak{color:#E66C2C;}");
      $this->add_css("#{$form->get_id()} .password_strength_checker.good{color:#2D98F3;}");
      $this->add_css("#{$form->get_id()} .password_strength_checker.strong{color:#006400;}");
    }

    parent::pre_render($form);
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();

    if(!isset($this->attributes['class'])) $this->attributes['class'] = '';
    if ($this->has_errors()) {
      $this->attributes['class'] .= ' has-errors';
    }
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes();
    $output = "<input type=\"password\" id=\"{$id}\" name=\"{$this->name}\" size=\"{$this->size}\" value=\"\"{$attributes} />\n";
    if($this->with_confirm == TRUE){
      $output .= "<label for=\"{$id}-confirm\">".$this->get_text($this->confirm_string)."</label>";
      $output .= "<input type=\"password\" id=\"{$id}-confirm\" name=\"{$this->name}_confirm\" size=\"{$this->size}\" value=\"\"{$attributes} />\n";
    }
    if($this->with_strength_check){
      $output .= "<span id=\"{$id}_result\" class=\"password_strength_checker\"></span>";
    }
    return $output;
  }

  /**
   * validate hook
   * @return boolean check if element is valid
   */
  public function valid(){
    if($this->with_confirm == TRUE){
      if(!isset($_REQUEST["{$this->name}_confirm"]) || $_REQUEST["{$this->name}_confirm"] != $this->value ) {
        $this->add_error($this->get_text("The passwords do not match"),__FUNCTION__);

        if($this->stop_on_first_error)
          return FALSE;
      }
    }
    return parent::valid();
  }

  /**
   * is_a_value hook
   * @return boolean this is a value
   */
  public function is_a_value(){
    return TRUE;
  }
}

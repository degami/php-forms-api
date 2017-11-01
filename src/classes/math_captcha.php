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
 * the image captcha field class
 */
 class math_captcha extends captcha{

   /**
    * captcha code
    * @var string
    */
   protected $code;

   /**
    * pre-fill code into textfield
    * @var boolean
    */
   protected $pre_filled = FALSE;

   private $a;
   private $b;
   private $op;

   private function get_math_code(){
     $this->code = '';
     $operators = ['+','-','*','/'];
     $this->a = mt_rand(0,50);
     $this->op = $operators[ mt_rand(0,count($operators)-1) ];

     $ret = NULL;
     do{
       $this->b = mt_rand( 1, 10);
       eval('$ret = '.$this->a.$this->op.$this->b.';');
     }while(!is_int($ret));

     $_SESSION['math_captcha_code'][$this->get_name()] = $this->a.$this->op.$this->b;

     if(mt_rand(0,1) == 0){ $this->code .= '<span class="nohm">'.mt_rand(1,10).$operators[ mt_rand(0,count($operators)-1) ].'</span>'; }
     $this->code .= $this->a;
     if(mt_rand(0,1) == 0){ $this->code .= '<span class="nohm">'.$operators[ mt_rand(0,count($operators)-1) ].mt_rand(1,10).'</span>'; }
     $this->code .= $this->op;
     if(mt_rand(0,1) == 0){ $this->code .= '<span class="nohm">'.mt_rand(1,10).$operators[ mt_rand(0,count($operators)-1) ].'</span>'; }
     $this->code .= $this->b;
     if(mt_rand(0,1) == 0){ $this->code .= '<span class="nohm">'.$operators[ mt_rand(0,count($operators)-1) ].mt_rand(1,10).'</span>'; }

     return $this->code;
   }

   /**
    * pre_render hook
    * @param  form $form form object
    */
   public function pre_render(form $form){
     if( $this->pre_rendered == TRUE ) return;
     $id = $this->get_html_id();
     $this->add_js("\$('#{$id} .nohm','#{$form->get_id()}').css({'display': 'none'});");

     parent::pre_render($form);
   }

   /**
    * render_field hook
    * @param  form $form form object
    * @return string        the element html
    */
   public function render_field(form $form) {
     $id = $this->get_html_id();
     $attributes = $this->get_attributes();
     $this->get_math_code();
     $codeval = '';
     if( $this->pre_filled == TRUE ) {
       eval('$codeval = '.$this->a.$this->op.$this->b.';');
     }
     $output = "<div id=\"{$id}\" {$attributes}>{$this->code}<br /><input type=\"text\" name=\"{$this->name}[code]\" value=\"{$codeval}\"  /></div>\n";
     return $output;
   }

   /**
    * validate hook
    * @return boolean TRUE if element is valid
    */
   public function valid() {
     if($this->already_validated == TRUE) return TRUE;
     if(isset($this->value['already_validated']) && $this->value['already_validated'] == TRUE) return TRUE;

     $_sessval = NULL;
     eval('$_sessval = '.$_SESSION['math_captcha_code'][$this->get_name()].';');
     if( isset($this->value['code']) && $this->value['code'] == $_sessval ) return TRUE;

     $this->add_error($this->get_text("Captcha response is not valid"), __FUNCTION__);
     return FALSE;
   }
 }

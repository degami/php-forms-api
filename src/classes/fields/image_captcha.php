<?php
/**
 * PHP FORMS API
 * @package degami/php-forms-api
 */
/* #########################################################
   ####                    FIELDS                       ####
   ######################################################### */

namespace Degami\PHPFormsApi\Fields;

use Degami\PHPFormsApi\form;
use Degami\PHPFormsApi\Base\field;
use Degami\PHPFormsApi\Abstracts\Fields\captcha;

/**
 * the image captcha field class
 */
 class image_captcha extends captcha{

   /**
    * output image type
    * @var string
    */
   protected $out_type = 'png';

   /**
    * availables characters for code
    * @var string
    */
   protected $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

   /**
    * minimum code length
    * @var integer
    */
   protected $min_length = 5;

   /**
    * maximum code length
    * @var integer
    */
   protected $max_length = 8;

   /**
    * image width
    * @var integer
    */
   protected $image_width = 100;

   /**
    * image height
    * @var integer
    */
   protected $image_height = 50;

   /**
    * text font size
    * @var integer
    */
   protected $font_size = 14;

   /**
    * pre-fill code into textfield
    * @var boolean
    */
   protected $pre_filled = FALSE;

   /**
    * captcha code
    * @var string
    */
   private $code;

   private function get_random_text(){
     $this->code = '';
     $length = mt_rand($this->min_length, $this->max_length);
     while( strlen($this->code) < $length ) {
         $this->code .= substr($this->characters, mt_rand() % (strlen($this->characters)), 1);
     }

     $_SESSION['image_captcha_code'][$this->get_name()] = $this->code;

     return $this->code;
   }

   private function get_random_color( $im ){
     // never white, never black
     return imagecolorallocate( $im, mt_rand(20,185),  mt_rand(20,185),  mt_rand(20,185) );
   }

   private function add_noise( $im ){
     for($i = 0; $i < $this->image_width; $i++) {
       for($j = 0; $j < $this->image_height; $j++) {
         if( (mt_rand(0,255) % mt_rand(7,11) == 0) && (mt_rand(0,1) == 1) ){
           $color = $this->get_random_color($im);
           imagesetpixel($im, $i, $j, $color);
         }
       }
     }
   }

   private function add_arcs($im){
     for ($i = 0; $i < 50; $i++) {
       //imagefilledrectangle($im, $i + $i2, 5, $i + $i3, 70, $black);
       imagesetthickness($im, rand(1,2));
       imagearc(
         $im,
         mt_rand(1, 300), // x-coordinate of the center.
         mt_rand(1, 300), // y-coordinate of the center.
         mt_rand(1, 300), // The arc width.
         mt_rand(1, 300), // The arc height.
         mt_rand(1, 300), // The arc start angle, in degrees.
         mt_rand(1, 300), // The arc end angle, in degrees.
         $this->get_random_color($im) // A color identifier.
       );
     }
   }

   private function get_image_string(){

     $text = $this->get_random_text();

     $im = imagecreate($this->image_width, $this->image_height);

     $white = imagecolorallocate($im, 255, 255, 255);
     $red = imagecolorallocate($im, 255, 0, 0);
     $grey = imagecolorallocate($im, 128, 128, 128);
     $black = imagecolorallocate($im, 0, 0, 0);

     $font = dirname(dirname(__FILE__)).'/fonts/Lato-Regular.ttf';
     imagefilledrectangle($im, 0, 0, $this->image_width, $this->image_height, $white);

     $x = 5;
     foreach( str_split($text) as $character ){
       $angle = mt_rand(-10,10);
       $size = mt_rand( $this->font_size - 4, $this->font_size + 4);
       $y = $this->image_height - $size - 5;
       $occ_space = (int)($size * 0.925);

       if( ($x + $occ_space + 5) > $this->image_width ){
         $new_width = $x + $occ_space + 5;
         $new_img = imagecreate( $new_width , $this->image_height );
         imagepalettecopy($new_img, $im);
         imagefilledrectangle($new_img, 0, 0, $new_width, $this->image_height, $white);

         imagecopy( $new_img, $im , 0 , 0 , 0 , 0, $this->image_width , $this->image_height);
         $this->image_width = $new_width;
         imagedestroy($im);
         $im = $new_img;
       }

       imagettftext($im, $size, $angle, $x+1, $y, $grey, $font, $character);
       imagettftext($im, $size, $angle, $x, $y+1, $this->get_random_color($im), $font, $character);

       $x += $occ_space;
     }

     $this->add_arcs($im);
     $this->add_noise($im);

     ob_start();
     switch($this->out_type){
       case 'jpg':
       case 'jpeg':
        $this->out_type = 'jpeg';
        imagejpeg($im);
        break;
      case 'gif':
       imagegif($im);
       break;
       case 'png':
       default:
        $this->out_type = 'png';
        imagepng($im);
        break;
     }
     $data = ob_get_contents();
     ob_end_clean();

     imagedestroy($im);

     $base64 = 'data:image/' . $this->out_type . ';base64,' . base64_encode($data);
     return $base64;
   }

   /**
    * render_field hook
    * @param  form $form form object
    * @return string        the element html
    */
   public function render_field(form $form) {
     $id = $this->get_html_id();
     $attributes = $this->get_attributes();
     $imagestring = $this->get_image_string();
     $codeval = $this->pre_filled == TRUE ? $this->code : '';
     $output = "<div {$attributes}><img src=\"".$imagestring."\" border=\"0\"><br /><input type=\"text\" id=\"{$id}\" name=\"{$this->name}[code]\" value=\"{$codeval}\"  /></div>\n";
     return $output;
   }

   /**
    * validate hook
    * @return boolean TRUE if element is valid
    */
   public function valid() {
     if($this->already_validated == TRUE) return TRUE;
     if(isset($this->value['already_validated']) && $this->value['already_validated'] == TRUE) return TRUE;
     if( isset($this->value['code']) && $this->value['code'] == $_SESSION['image_captcha_code'][$this->get_name()] ) return TRUE;

     $this->add_error($this->get_text("Captcha response is not valid"), __FUNCTION__);
     return FALSE;
   }
 }

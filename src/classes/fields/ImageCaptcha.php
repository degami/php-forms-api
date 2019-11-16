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
use Degami\PHPFormsApi\Accessories\TagElement;
use Degami\PHPFormsApi\Abstracts\Fields\Captcha;
use Degami\PHPFormsApi\FormBuilder;

/**
 * The image captcha field class
 */
class ImageCaptcha extends Captcha
{
    /**
     * output image type
     *
     * @var string
     */
    protected $out_type = 'png';

    /**
     * availables characters for code
     *
     * @var string
     */
    protected $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

    /**
     * minimum code length
     *
     * @var integer
     */
    protected $min_length = 5;

    /**
     * maximum code length
     *
     * @var integer
     */
    protected $max_length = 8;

    /**
     * image width
     *
     * @var integer
     */
    protected $image_width = 100;

    /**
     * image height
     *
     * @var integer
     */
    protected $image_height = 50;

    /**
     * text font size
     *
     * @var integer
     */
    protected $font_size = 14;

    /**
     * pre-fill code into textfield
     *
     * @var boolean
     */
    protected $pre_filled = false;

    /**
     * captcha code
     *
     * @var string
     */
    private $code;

    /**
     * Gets a random text
     *
     * @return string text
     */
    private function getRandomText()
    {
        $this->code = '';
        $length = mt_rand($this->min_length, $this->max_length);
        while (strlen($this->code) < $length) {
            $this->code .= substr($this->characters, mt_rand() % (strlen($this->characters)), 1);
        }

        if (FormBuilder::sessionPresent()) {
            $this->getSessionBag()->ensurePath("/image_captcha_code");
            $this->getSessionBag()->image_captcha_code->{$this->getName()} = $this->code;
        }

        return $this->code;
    }

    /**
     * Gets a random color
     *
     * @param  resource $im image resource
     * @return integer
     */
    private function getRandomColor($im)
    {
        // never white, never black
        return imagecolorallocate($im, mt_rand(20, 185), mt_rand(20, 185), mt_rand(20, 185));
    }

    /**
     * Adds noise to image
     *
     * @param resource $im image resource
     */
    private function addNoise($im)
    {
        for ($i = 0; $i < $this->image_width; $i++) {
            for ($j = 0; $j < $this->image_height; $j++) {
                if ((mt_rand(0, 255) % mt_rand(7, 11) == 0) && (mt_rand(0, 1) == 1)) {
                    $color = $this->getRandomColor($im);
                    imagesetpixel($im, $i, $j, $color);
                }
            }
        }
    }

    /**
     * Adds arcs to image
     *
     * @param resource $im image resource
     */
    private function addArcs($im)
    {
        for ($i = 0; $i < 50; $i++) {
            //imagefilledrectangle($im, $i + $i2, 5, $i + $i3, 70, $black);
            imagesetthickness($im, rand(1, 2));
            imagearc(
                $im,
                mt_rand(1, 300), // x-coordinate of the center.
                mt_rand(1, 300), // y-coordinate of the center.
                mt_rand(1, 300), // The arc width.
                mt_rand(1, 300), // The arc height.
                mt_rand(1, 300), // The arc start angle, in degrees.
                mt_rand(1, 300), // The arc end angle, in degrees.
                $this->getRandomColor($im) // A color identifier.
            );
        }
    }

    /**
     * Gets image as base64 string
     *
     * @return string image representation as base64 string
     */
    private function getImageString()
    {
        $text = $this->getRandomText();

        $im = imagecreate($this->image_width, $this->image_height);

        $white = imagecolorallocate($im, 255, 255, 255);
        $grey = imagecolorallocate($im, 128, 128, 128);

        $font = dirname(dirname(dirname(__FILE__))).'/fonts/Lato-Regular.ttf';
        imagefilledrectangle($im, 0, 0, $this->image_width, $this->image_height, $white);

        $x = 5;
        foreach (str_split($text) as $character) {
            $angle = mt_rand(-10, 10);
            $size = mt_rand($this->font_size - 4, $this->font_size + 4);
            $y = $this->image_height - $size - 5;
            $occ_space = (int)($size * 0.925);

            if (($x + $occ_space + 5) > $this->image_width) {
                $new_width = $x + $occ_space + 5;
                $new_img = imagecreate($new_width, $this->image_height);
                imagepalettecopy($new_img, $im);
                imagefilledrectangle($new_img, 0, 0, $new_width, $this->image_height, $white);

                imagecopy($new_img, $im, 0, 0, 0, 0, $this->image_width, $this->image_height);
                $this->image_width = $new_width;
                imagedestroy($im);
                $im = $new_img;
            }

            imagettftext($im, $size, $angle, $x+1, $y, $grey, $font, $character);
            imagettftext($im, $size, $angle, $x, $y+1, $this->getRandomColor($im), $font, $character);

            $x += $occ_space;
        }

        $this->addArcs($im);
        $this->addNoise($im);

        ob_start();
        switch ($this->out_type) {
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
     * {@inheritdoc}
     *
     * @param Form $form form object
     *
     * @return string        the element html
     */
    public function renderField(Form $form)
    {
        $id = $this->getHtmlId();
//        $attributes = $this->getAttributes();
        $imagestring = $this->getImageString();
        $codeval = $this->pre_filled == true ? $this->code : '';

        $tag = new TagElement([
            'tag' => 'div',
            'id' => $id,
            'attributes' => $this->attributes,
        ]);
        $tag->addChild(new TagElement([
            'tag' => 'img',
            'attributes' => ['src' => $imagestring, 'border' => 0],
        ]));
        $tag->addChild(new TagElement([
            'tag' => 'input',
            'type' => 'text',
            'name' => $this->name."[code]",
            'value' => $codeval,
        ]));

        if (!FormBuilder::sessionPresent()) {
            $tag->addChild(new TagElement([
                'tag' => 'input',
                'type' => 'hidden',
                'name' => $this->name."[code_chk]",
                'attributes' => [
                    'class' => FORMS_FIELD_ADDITIONAL_CLASS.' hidden',
                ],
                'value' => sha1($this->code . substr(md5(static::class), 0, 5)),
            ]));
        }
        return $tag;
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean TRUE if element is valid
     */
    public function isValid()
    {
        if ($this->already_validated == true) {
            return true;
        }
        if (isset($this->value['already_validated']) && $this->value['already_validated'] == true) {
            return true;
        }
        
        if (!FormBuilder::sessionPresent()) {
            if (isset($this->value['code']) && isset($this->value['code_chk'])
                && sha1($this->value['code'].substr(md5(static::class), 0, 5)) == $this->value['code_chk']
            ) {
                return true;
            }
        } else {
            if (isset($this->value['code'])
                && $this->value['code'] == $this->getSessionBag()->image_captcha_code->{$this->getName()}
            ) {
                return true;
            }
        }

        $this->addError($this->getText("Captcha response is not valid"), __FUNCTION__);
        return false;
    }
}

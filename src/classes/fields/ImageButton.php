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
use Degami\PHPFormsApi\Abstracts\Fields\Clickable;

/**
 * The image submit input type field class
 */
class ImageButton extends Clickable
{
    /**
     * image source
     *
     * @var string
     */
    protected $src;

    /**
     * image alternate
     *
     * @var string
     */
    protected $alt;

    /**
     * Class constructor
     *
     * @param array  $options build options
     * @param string $name    field name
     */
    public function __construct($options = [], $name = null)
    {
        $this->default_value = [
            'x'=>-1,
            'y'=>-1,
        ];

        parent::__construct($options, $name);
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
        if ($this->disabled == true) {
            $this->attributes['disabled']='disabled';
        }

        $tag = new TagElement(
            [
                'tag' => 'input',
                'type' => 'image',
                'id' => $id,
                'name' => $this->name,
                'value_needed' => false,
                'attributes' => $this->attributes + ['src' => $this->src, 'alt' => $this->alt],
            ]
        );
        return $tag;
    }

    /**
     * {@inheritdoc}
     *
     * @param array $request request array
     */
    public function alterRequest(&$request)
    {
        foreach ($request as $key => $val) {
            //IMAGE BUTTONS HANDLE
            if (preg_match('/^(.*?)_(x|y)$/', $key, $matches) && $this->getName() == $matches[1]) {
                //assume this is an input type="image"
                if (isset($request[$matches[1].'_'.(($matches[2] == 'x')?'y':'x')])) {
                    $request[$matches[1]] = [
                        'x'=>$request[$matches[1].'_x'],
                        'y'=>$request[$matches[1].'_y'],
                    ];

                    unset($request[$matches[1].'_x']);
                    unset($request[$matches[1].'_y']);
                }
            }
        }
    }
}

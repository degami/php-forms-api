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
   ####                 ACCESSORIES                     ####
   ######################################################### */

namespace Degami\PHPFormsApi\Accessories;

use Degami\PHPFormsApi\Traits\Tools;
use Degami\PHPFormsApi\Interfaces\TagInterface;
use Degami\PHPFormsApi\Abstracts\Base\BaseElement;

/**
 * A class to render form fields tags
 */
class TagList extends BaseElement implements TagInterface
{
    use Tools;

    /** @var array tag children */
    protected $children;

    /**
     * Class constructor
     *
     * @param array $options build options
     */
    public function __construct($options = [])
    {
        $this->children = [];
        $this->setClassProperties($options);
    }

    /**
     * Gets html tag string
     *
     * @return string tag html representation
     */
    public function renderTag()
    {
        $out = "";
        foreach ($this->children as $key => $value) {
            if ($value instanceof TagElement) {
                $out .= $value->renderTag();
            }
        }
        return $out;
    }

    /**
     * Add child to tag
     *
     * @param  TagElement $child child to add
     * @return TagList
     */
    public function addChild($child)
    {
        if ($child instanceof TagElement) {
            $this->children[] = $child;
        }
        return $this;
    }

    /**
     * toString magic method
     *
     * @return string the tag html
     */
    public function __toString()
    {
        try {
            return $this->renderTag();
        } catch (\Exception $e) {
            return $e->getMessage()."\n".$e->getTraceAsString();
        }
    }
}

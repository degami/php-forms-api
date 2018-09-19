<?php
/**
 * PHP FORMS API
 *
 * @package degami/php-forms-api
 */
/* #########################################################
// ACCESSORIES                     ####
// */

namespace Degami\PHPFormsApi\Accessories;

use Degami\PHPFormsApi\Interfaces\TagInterface;
use Degami\PHPFormsApi\Abstracts\Base\BaseElement;

/**
 * a class to render form fields tags
 */
class TagElement extends BaseElement implements TagInterface
{
    public static $closed_tags = [
        'textarea','select','option','optgroup','datalist','button','fieldset','legend',
        'div', 'span', 'table', 'thead', 'tbody', 'tr', 'td','h3','ul','li',
    ];

    public static $novalue_tags = [
        'textarea','select','optgroup','datalist','fieldset','legend',
        'div', 'span', 'table', 'thead', 'tbody', 'tr', 'td','h3','ul','li',
    ];

    protected $tag;
    protected $type;
    protected $name;
    protected $id;
    protected $value;
    protected $text;
    protected $children;
    protected $reserved_attributes = ['type','name', 'id','value'];
    protected $has_close = null;
    protected $value_needed = true;


    /**
     * class constructor
     *
     * @param array $options build options
     */
    public function __construct($options = [])
    {
        $this->tag = '';

        $this->type = '';
        $this->name = '';
        $this->id = '';
        $this->value = '';

        $this->text = '';
        $this->children = [];

        if (isset($options['tag'])) {
            $this->tag = trim(strtolower($options['tag']));
            unset($options['tag']);
        }
        if (isset($options['reserved_attributes'])) {
            $this->reserved_attributes = $options['reserved_attributes'];
            unset($options['reserved_attributes']);
        }

        if (in_array($this->tag, static::$novalue_tags)) {
            $this->value_needed = false;
        }

        if (in_array($this->tag, static::$closed_tags)) {
            $this->has_close = true;
        }

        foreach ($this->reserved_attributes as $key) {
            if (isset($options[$key])) {
                if (property_exists(get_class($this), $key)) {
                    $this->{$key} = $options[$key];
                    unset($options[$key]);
                }
            }
        }

        if (isset($options['children']) && !isset($options['has_close'])) {
            if (!empty($options['children'])) {
                $this->has_close = true;
            }
        }

        foreach ($options as $name => $value) {
            $name = trim($name);
            if (property_exists(get_class($this), $name)) {
                $this->{$name} = $value;
            }
        }

        if (!isset($this->attributes['class'])) {
            $this->attributes['class'] = $this->getElementClassName();
        }
    }

    public function getElementClassName()
    {
        return strtolower($this->tag == 'input' ? $this->type : $this->tag);
    }

    public function renderTag()
    {
        static::executeAlter("/.*?_before_render_".$this->tag."_alter$/i", [&$this]);
        $reserved_attributes = "";
        foreach ($this->reserved_attributes as $key) {
            if (property_exists(get_class($this), $key) &&
                (!empty($this->{$key}) || $key == 'value' &&
                $this->getValueNeeded())
            ) {
                $reserved_attributes .= ' '.$key.'="'.$this->{$key}.'"';
            }
        }
        $attributes = $this->getAttributes($this->reserved_attributes);
        return "<{$this->tag}{$reserved_attributes}{$attributes}".($this->has_close ? ">" : "/>").
        $this->text.
        ($this->has_close ? $this->renderChildren()."</{$this->tag}>" : "");
    }

    public function addChild($child)
    {
        $this->children[] = $child;
        $this->has_close = true;
        return $this;
    }

    private function renderChildren()
    {
        $out = "";
        foreach ($this->children as $key => $value) {
            if ($value instanceof TagElement) {
                $out .= $value->renderTag();
            } elseif (is_scalar($value)) {
                $out .= $value;
            }
        }
        return $out;
    }

    /**
     * Return if value attribute is nneeded
     *
     * @return bool
     */
    private function getValueNeeded()
    {
        return $this->value_needed;
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

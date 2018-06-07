<?php
/**
* PHP FORMS API
* @package degami/php-forms-api
*/
/* #########################################################
####                 ACCESSORIES                     ####
######################################################### */

namespace Degami\PHPFormsApi\Accessories;

use Degami\PHPFormsApi\Interfaces\tag_interface;
use Degami\PHPFormsApi\Abstracts\Base\base_element;

/**
* a class to render form fields tags
*/
class tag_element extends base_element implements tag_interface{

	static $closed_tags = ['textarea','select','option','optgroup','datalist','button'];

	protected $tag;
	protected $type;
	protected $name;
	protected $id;
	protected $value;
	protected $text;
	protected $children;
	protected $reserved_attributes = ['type','name', 'id','value'];
	protected $has_close = NULL;
	protected $value_needed = TRUE;


/**
* class constructor
* @param array  $options build options
*/
public function __construct($options = []) {
		$this->tag = '';

		$this->type = '';
		$this->name = '';
		$this->id = '';
		$this->value = '';

		$this->text = '';
		$this->children = [];

		if( isset($options['tag']) ){
			$this->tag = trim(strtolower( $options['tag'] ));
			unset($options['tag']);
		}
		if( isset($options['reserved_attributes']) ){
			$this->reserved_attributes = $options['reserved_attributes'];
			unset($options['reserved_attributes']);
		}

		if(in_array( $this->tag, ['textarea','select'] )){
			$this->value_needed = FALSE;
		}

		if(in_array($this->tag, static::$closed_tags)){
			$this->has_close = TRUE;			
		}

		foreach($this->reserved_attributes as $key){
			if(isset($options[$key])){
				if( property_exists(get_class($this), $key) ){
					$this->{$key} = $options[$key];
					unset($options[$key]);					
				}
			}
		}

		if(isset($options['children']) && !isset($options['has_close'])){
			if(!empty($options['children'])){
				$this->has_close = TRUE;				
			}
		}

		foreach ($options as $name => $value) {
			$name = trim($name);
			if( property_exists(get_class($this), $name) )
				$this->{$name} = $value;
		}

		if(!isset($this->attributes['class'])){
			$this->attributes['class'] = $this->get_element_class_name();
		}
	}

	public function get_element_class_name(){
		return strtolower( $this->tag == 'input' ? $this->type : $this->tag );
	}

	public function render_tag(){
		$reserved_attributes = "";
		foreach ($this->reserved_attributes as $key) {
			if( property_exists(get_class($this), $key) && (!empty($this->{$key}) || $key == 'value' && $this->get_value_needed()) ){
				$reserved_attributes .= ' '.$key.'="'.$this->{$key}.'"';
			}
		}
		$attributes = $this->get_attributes($this->reserved_attributes);
		return "<{$this->tag}{$reserved_attributes}{$attributes}".($this->has_close ? ">" : "/>").
		$this->text.
		($this->has_close ? $this->render_children()."</{$this->tag}>" : "");
	}

	public function add_child($child) {
		$this->children[] = $child;
		$this->has_close = TRUE;
		return $this;
	}

	private function render_children(){
		$out = "";
		foreach ($this->children as $key => $value) {
			if($value instanceof tag_element) $out .= $value->render_tag();
	// else if( $value instanceof field) $out .= $value->render();
			else if( is_scalar($value)) $out .= $value;
		}
		return $out;
	}
}

<?php
/**
 * PHP FORMS API
 * @package degami/php-forms-api
 */
/* #########################################################
   ####              FIELD CONTAINERS                   ####
   ######################################################### */

namespace Degami\PHPFormsApi;
use \Exception;

/**
 * a nestable field container
 */
class nestable extends fields_container {
  public $level = 0;
  public $childnum = 0;
  public $tag = 'ol';
  public $tagclass = 'dd-list';
  public $children = array();
  public $fields_panel = NULL;
  public $maxDepth = 5;
  public static $_groupcounter = 0;
  public static $_css_rendered = FALSE;
  public $group = 0;

  public function __construct($options = array(), $name = NULL){
    parent::__construct($options, $name);
    $this->fields_panel = new tag_container(array(
      'type' => 'tag_container',
      'tag' => 'div',
      'container_class' => '',
      'container_tag' => '',
      'prefix' => '<div class="dd-panel"><div class="dd-handle" style="vertical-align: top;display: inline-block;">&nbsp;</div><div class="dd-content">',
      'suffix' => '</div></div>',
      'attributes' => array('class' => 'level-'.$this->level),
    ),'panel-'.$this->get_level().'-'.$this->get_name());

    parent::add_field($this->fields_panel->get_name(), $this->fields_panel);

    $this->group = nestable::$_groupcounter++;
  }

  public function get_level(){
    return $this->level;
  }

  public function add_child($tag = NULL, $tagclass = NULL){
    if($tag == NULL) $tag = $this->tag;
    if($tagclass == NULL) $tagclass = $this->tagclass;

    $nextchild = new nestable(array(
      'type' => 'nestable',
      'level' => $this->level+1,
      'tag' => $tag,
      'container_class' => '',
      'container_tag' => '',
      'attributes' => array('class' => $tagclass),
      'childnum' => $this->num_children(),
    ),
      //'leaf-'.$this->get_level().'-'.$this->num_children()
      $this->get_name().'-leaf-'. $this->num_children()
    );

    $this->children[] = $nextchild;
    parent::add_field($nextchild->get_name(), $nextchild);

    return $this->children[$this->num_children()-1];
  }

  public function num_children() {
    return count($this->get_children());
  }

  public function has_children(){
    return $this->num_children() > 0;
  }

  public function &get_child($numchild){
    return isset($this->children[$numchild]) ? $this->children[$numchild] : FALSE;
  }

  public function &get_children(){
    return $this->children;
  }

  public function add_field($name, $field){
    $field_type = NULL;
    if (!is_object($field)) {
      $field_type = __NAMESPACE__ . "\\" . ( isset($field['type']) ? "{$field['type']}" : 'textfield' );
    }else{
      $field_type = get_class($field);
    }

    if(!class_exists($field_type)){
      throw new Exception("Error adding field. Class $field_type not found", 1);
    }

    $fakefield = new $field_type($field, 'fake_'.$name);
    if($fakefield instanceof fields_container && !( $fakefield instanceof geolocation || $fakefield instanceof datetime ) ){
      throw new Exception("Can't add a fields_container to a tree_container.", 1);
    }

    $this->fields_panel->add_field($name, $field);
    return $this;
  }

  /**
   * remove field from form
   * @param  string $field field name
   */
  public function remove_field($name){
    $this->fields_panel->remove_field($name);
    return $this;
  }

  public function process($values){
    parent::process( $values );
    if(isset($values[$this->get_name()])){
      $this->value = json_decode($values[$this->get_name()], TRUE);
      //$this->value[0]['values'] = nestable::find_by_id($this->value[0]['id']);
    }
  }

  private function get_panel_by_id($nestableid){
    if($this->get_html_id() == $nestableid) return $this->fields_panel;
    foreach ($this->get_children() as $key => $child) {
      $return = $child->get_panel_by_id($nestableid);
      if( $return != FALSE ) return $return;
    }
    return FALSE;
  }

  private static function create_values_array( $tree, nestable $nestablefield ){
    $out = array();
    $panel = $nestablefield->get_panel_by_id($tree['id']);
    if( $panel instanceof fields_container ){
      //$out[$tree['id']]['value'] = $panel->values();
      $out['value'] = $panel->values();
      if(isset($tree['children'])){
        foreach($tree['children'] as $child){
          //$out[$tree['id']]['children'][] = nestable::create_values_array($child, $nestablefield);
          $out['children'][] = nestable::create_values_array($child, $nestablefield);
        }
      }
    }
    return $out;
  }

  public function values(){
    if($this->value) {
      // return $this->value;
      // var_dump($this->value);die();
      $out = array();
      foreach($this->value as $tree){
        // $out = array_merge($out, nestable::create_values_array($tree, $this) );
        $out[] = nestable::create_values_array($tree, $this);
      }
      return $out;
    }
    return parent::values();
  }

  public function render_field(form $form){
    if(!isset($this->attributes['class'])) $this->attributes['class'] = '';
    $this->attributes['class'] .= ' '.$this->tagclass;
    $id = $this->get_html_id();

    $attributes = $this->get_attributes();
    $out = "";
    if($this->get_level() == 0) $out .= "<div class=\"dd\" id=\"{$id}\"><{$this->tag}{$attributes}>";
    $out .= '<li class="dd-item level-'.$this->level.' child-'.$this->childnum.'" data-id="'.$id.'">';
    $out .= $this->fields_panel->render($form);
    if( $this->has_children() ) {
      $out .= "<{$this->tag} {$attributes}>";
      $children = $this->get_children();
      foreach ($children as $key => &$child) {
        $out .= $child->render($form);
      }
      $out .= "</{$this->tag}>";
    }
    $out .= '</li>';

    if($this->get_level() == 0) $out .= "</{$this->tag}></div><textarea name=\"{$this->get_name()}\" id=\"{$id}-output\" style=\"display: none; width: 100%; height: 200px;\"></textarea>";

    return $out;
  }

  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();
    if($this->get_level() == 0){

      $this->add_js(preg_replace("/\s+/"," ",str_replace("\n","","".
        "\$('#{$id}','#{$form->get_id()}').data('output', \$('#{$id}-output'));
       \$('#{$id}','#{$form->get_id()}').nestable({group: {$this->group}, maxDepth: {$this->maxDepth} }).on('change', function(e){
        var list   = e.length ? e : $(e.target),
        output = list.data('output');
        if (window.JSON) {
            output.val(window.JSON.stringify(list.nestable('serialize')));
        } else {
            output.val('JSON browser support required for this.');
        }
      }).trigger('change');"
      )));

      if(!nestable::$_css_rendered){

        $this->add_css('
.dd { position: relative; display: block; margin: 0; padding: 0; list-style: none; font-size: 13px; line-height: 20px; }

.dd-list { display: block; position: relative; margin: 0; padding: 0; list-style: none; }
.dd-list .dd-list { padding-left: 30px; }
.dd-collapsed .dd-list { display: none; }
.dd-item,.dd-empty,.dd-placeholder { display: block; position: relative; margin: 10px 0 0 0; padding: 0; min-height: 20px; font-size: 13px; line-height: 20px; }

.dd-handle { display: block; margin: 5px 0; padding: 5px 10px; color: #333; text-decoration: none; font-weight: bold; border: 1px solid #ccc;
    background: #fafafa;
    background: -webkit-linear-gradient(top, #fafafa 0%, #eee 100%);
    background:    -moz-linear-gradient(top, #fafafa 0%, #eee 100%);
    background:         linear-gradient(top, #fafafa 0%, #eee 100%);
    -webkit-border-radius: 3px;
            border-radius: 3px;
    box-sizing: border-box; -moz-box-sizing: border-box;
}
.dd-handle:hover { color: #2ea8e5; background: #fff; }

.dd-item > button { display: block; position: relative; cursor: pointer; z-index: 20; float: left; width: 25px; height: 20px; margin: 5px 0; padding: 0; text-indent: 100%; white-space: nowrap; overflow: hidden; border: 0; background: transparent; font-size: 12px; line-height: 1; text-align: center; font-weight: bold; }
.dd-item > button:before { content: \'+\'; display: block; position: absolute; width: 100%; text-align: center; text-indent: 0; }
.dd-item > button[data-action="collapse"]:before { content: \'-\'; }

.dd-placeholder,
.dd-empty { margin: 5px 0; padding: 0; min-height: 30px; background: #f2fbff; border: 1px dashed #b6bcbf; box-sizing: border-box; -moz-box-sizing: border-box; display: block; }
.dd-empty {
    border: 1px dashed #bbb; min-height: 100px; background-color: #e5e5e5;
    background-image: -webkit-linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff),
                      -webkit-linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff);
    background-image:    -moz-linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff),
                         -moz-linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff);
    background-image:         linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff),
                              linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff);
    background-size: 60px 60px;
    background-position: 0 0, 30px 30px;
}

.dd-dragel { position: absolute; pointer-events: none; z-index: 9999; }
.dd-dragel  > .dd-panel > .dd-item .dd-handle { margin-top: 0; }
.dd-dragel .dd-handle {
    -webkit-box-shadow: 2px 4px 6px 0 rgba(0,0,0,.1);
            box-shadow: 2px 4px 6px 0 rgba(0,0,0,.1);
}

.dd-panel{ position: relative; }
.dd-content { display: block; min-height: 30px; margin: 5px 0; padding: 5px 10px 5px 40px; color: #333; text-decoration: none; font-weight: bold; border: 1px solid #ccc;
    background: #fafafa;
    background: -webkit-linear-gradient(top, #fafafa 0%, #eee 100%);
    background:    -moz-linear-gradient(top, #fafafa 0%, #eee 100%);
    background:         linear-gradient(top, #fafafa 0%, #eee 100%);
    -webkit-border-radius: 3px;
            border-radius: 3px;
    box-sizing: border-box; -moz-box-sizing: border-box;
}
.dd-content:hover { color: #2ea8e5; background: #fff; }
.dd-dragel > .dd-item > .dd-panel > .dd-content { margin: 0; }
.dd-item > button { margin-left: 30px; }

.dd-handle { position: absolute; margin: 0; left: 0; top: 0; cursor: pointer; width: 30px; text-indent: 100%; white-space: nowrap; overflow: hidden;
  border: 1px solid #aaa;
  background: #ddd;
  background: -webkit-linear-gradient(top, #ddd 0%, #bbb 100%);
  background:    -moz-linear-gradient(top, #ddd 0%, #bbb 100%);
  background:         linear-gradient(top, #ddd 0%, #bbb 100%);
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
  height: 100%;
}
.dd-handle:before { content: \'≡\'; display: block; position: absolute; left: 0; top: 3px; width: 100%; text-align: center; text-indent: 0; color: #fff; font-size: 20px; font-weight: normal; }
.dd-handle:hover { background: #ddd; }
');
        nestable::$_css_rendered = TRUE;
      }

    }

    parent::pre_render($form);
  }

}

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
   ####              FIELD CONTAINERS                   ####
   ######################################################### */

namespace Degami\PHPFormsApi\Containers;

use Degami\Basics\Html\BaseElement;
use Degami\PHPFormsApi\Abstracts\Base\Field;
use Degami\PHPFormsApi\Form;
use Degami\PHPFormsApi\Abstracts\Base\FieldsContainer;
use Degami\PHPFormsApi\Traits\Containers;
use Degami\PHPFormsApi\Exceptions\FormException;

/**
 * a nestable field container
 */
class Nestable extends FieldsContainer
{
    use Containers;

    /** @var integer level*/
    public $level = 0;

    /** @var integer number of children */
    public $child_num = 0;

    /** @var string tag for list */
    public $tag = 'ol';

    /** @var string css class for list */
    public $tag_class = 'dd-list';

    /** @var array children */
    public $children = [];

    /** @var TagContainer panel */
    public $fields_panel = null;

    /** @var integer maximum depth */
    public $maxDepth = 5;

    /** @var integer group counter */
    public static $group_counter = 0;

    /** @var boolean css has been rendered flag */
    public static $css_rendered = false;

    /** @var integer group */
    public $group = 0;

    /**
     * Nestable constructor.
     *
     * @param array $options
     * @param null  $name
     *
     * @throws FormException
     */
    public function __construct($options = [], $name = null)
    {
        parent::__construct($options, $name);
        $this->fields_panel = new TagContainer(
            [
                'type' => 'tag_container',
                'tag' => 'div',
                'container_class' => '',
                'container_tag' => '',
                'prefix' => '<div class="dd-panel">
                              <div class="dd-handle" style="vertical-align: top;display: inline-block;">&nbsp;</div>
                              <div class="dd-content">',
                'suffix' => ' </div>
                             </div>',
                'attributes' => ['class' => 'level-'.$this->level],
            ],
            'panel-'.$this->getLevel().'-'.$this->getName()
        );

        parent::addField($this->fields_panel->getName(), $this->fields_panel);

        $this->group = Nestable::$group_counter++;
    }

    /**
     * Get level
     *
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * Add child
     *
     * @param ?string $tag
     * @param ?string $tag_class
     *
     * @return mixed
     * @throws FormException
     */
    public function addChild($tag = null, $tag_class = null): Nestable
    {
        if ($tag == null) {
            $tag = $this->tag;
        }
        if ($tag_class == null) {
            $tag_class = $this->tag_class;
        }

        $nextchild = new Nestable(
            [
                'type' => 'nestable',
                'level' => $this->level+1,
                'tag' => $tag,
                'container_class' => '',
                'container_tag' => '',
                'attributes' => ['class' => $tag_class],
                'child_num' => $this->numChildren(),
            ],
            $this->getName().'-leaf-'. $this->numChildren()
        );

        $this->children[] = $nextchild;
        parent::addField($nextchild->getName(), $nextchild);

        return $this->children[$this->numChildren()-1];
    }

    /**
     * Get children count
     *
     * @return int
     */
    public function numChildren(): int
    {
        return count($this->getChildren());
    }

    /**
     * Check if there are children
     *
     * @return bool
     */
    public function hasChildren(): bool
    {
        return $this->numChildren() > 0;
    }

    /**
     * Get a child
     *
     * @param $num_child
     *
     * @return bool|mixed
     */
    public function &getChild($num_child): bool
    {
        $out = isset($this->children[$num_child]) ? $this->children[$num_child] : false;
        return $out;
    }

    /**
     * Get all children
     *
     * @return array
     */
    public function &getChildren(): array
    {
        return $this->children;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $name
     * @param mixed  $field
     *
     * @return $this|Field
     * @throws FormException
     */
    public function addField(string $name, $field) : Field
    {
        $field = $this->getFieldObj($name, $field);
        if (!($field instanceof Nestable) && $this->isFieldContainer($field)) {
            throw new FormException("Can't add a fields_container to a tree_container.", 1);
        }

        $this->fields_panel->addField($name, $field);
        return $this;
    }

    /**
     * remove field from form
     *
     * @param string $name field name
     * @return self
     */
    public function removeField(string $name) : FieldsContainer
    {
        $this->fields_panel->removeField($name);
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $values
     */
    public function processValue($values)
    {
        parent::processValue($values);
        if (isset($values[$this->getName()])) {
            $this->value = json_decode($values[$this->getName()], true);
        }
    }

    /**
     * Get a panel
     *
     * @param string $nestable_id
     * @return bool|TagContainer|null
     */
    private function getPanelById(string $nestable_id)
    {
        if ($this->getHtmlId() == $nestable_id) {
            return $this->fields_panel;
        }
        foreach ($this->getChildren() as $key => $child) {
            $return = $child->getPanelById($nestable_id);
            if ($return != false) {
                return $return;
            }
        }
        return false;
    }

    /**
     * create values array
     *
     * @param  array    $tree          tree
     * @param  Nestable $nestable_field field
     * @return array    values array
     */
    private static function createValuesArray(array $tree, Nestable $nestable_field): array
    {
        $out = [];
        $panel = $nestable_field->getPanelById($tree['id']);
        if ($panel instanceof FieldsContainer) {
            $out['value'] = $panel->getValues();
            if (isset($tree['children'])) {
                foreach ($tree['children'] as $child) {
                    $out['children'][] = Nestable::createValuesArray($child, $nestable_field);
                }
            }
        }
        return $out;
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed
     */
    public function getValues()
    {
        if ($this->value) {
            $out = [];
            foreach ($this->value as $tree) {
                $out[] = Nestable::createValuesArray($tree, $this);
            }
            return $out;
        }
        return parent::getValues();
    }

    /**
     * {@inheritdoc}
     *
     * @param Form $form
     *
     * @return string|BaseElement
     */
    public function renderField(Form $form)
    {
        if (!isset($this->attributes['class'])) {
            $this->attributes['class'] = '';
        }
        $this->attributes['class'] .= ' '.$this->tag_class;
        $id = $this->getHtmlId();

        $attributes = $this->getAttributes();
        $out = "";
        if ($this->getLevel() == 0) {
            $out .= "<div class=\"dd\" id=\"{$id}\"><{$this->tag}{$attributes}>";
        }
        $out .= '<li class="dd-item level-'.$this->level.' child-'.$this->child_num.'" data-id="'.$id.'">';
        $out .= $this->fields_panel->renderHTML($form);
        if ($this->hasChildren()) {
            $out .= "<{$this->tag} {$attributes}>";
            $children = $this->getChildren();
            foreach ($children as $key => &$child) {
                $out .= $child->renderHTML($form);
            }
            $out .= "</{$this->tag}>";
        }
        $out .= '</li>';

        if ($this->getLevel() == 0) {
            $out .= "</{$this->tag}>
                    </div>
                    <textarea name=\"{$this->getName()}\" id=\"{$id}-output\"
                              style=\"display: none; width: 100%; height: 200px;\"></textarea>";
        }

        return $out;
    }

    /**
     * {@inheritdoc}
     *
     * @param Form $form
     */
    public function preRender(Form $form)
    {
        if ($this->pre_rendered == true) {
            return;
        }
        $id = $this->getHtmlId();
        if ($this->getLevel() == 0) {
            $this->addJs(
                "\$('#{$id}','#{$form->getId()}').data('output', \$('#{$id}-output'));
            \$('#{$id}','#{$form->getId()}').nestable({group: {$this->group}, maxDepth: {$this->maxDepth} })
            .on('change', function(e){
                var list   = e.length ? e : $(e.target),
                output = list.data('output');
                if (window.JSON) {
                    output.val(window.JSON.stringify(list.nestable('serialize')));
                } else {
                    output.val('JSON browser support required for this.');
                }
            })
            .trigger('change');"
            );

            if (!Nestable::$css_rendered) {
                $this->addCss(
                    '
.dd { position: relative; display: block; margin: 0; padding: 0; list-style: none; font-size: 13px; line-height: 20px; }

.dd-list { display: block; position: relative; margin: 0; padding: 0; list-style: none; }
.dd-list .dd-list { padding-left: 30px; }
.dd-collapsed .dd-list { display: none; }
.dd-item,.dd-empty,.dd-placeholder {
    display: block; position: relative; margin: 10px 0 0 0; padding: 2px 0 0 0;
    min-height: 20px; font-size: 13px; line-height: 20px;
}

.dd-handle { display: block; margin: 5px 0; padding: 5px 10px; color: #333; text-decoration: none; font-weight: bold;
                border: 1px solid #ccc;
    background: #fafafa;
    background: -webkit-linear-gradient(top, #fafafa 0%, #eee 100%);
    background:    -moz-linear-gradient(top, #fafafa 0%, #eee 100%);
    background:         linear-gradient(top, #fafafa 0%, #eee 100%);
    -webkit-border-radius: 3px;
            border-radius: 3px;
    box-sizing: border-box; -moz-box-sizing: border-box;
}
.dd-handle:hover { color: #2ea8e5; background: #fff; }

.dd-item > button { display: block; position: relative; cursor: pointer; z-index: 20; float: left; width: 25px;
                    height: 20px; margin: 5px 0; padding: 0; text-indent: 100%; white-space: nowrap; overflow: hidden;
                    border: 0; background: transparent; font-size: 12px; line-height: 1; text-align: center;
                    font-weight: bold; }
.dd-item > button:before { content: \'+\'; display: block; position: absolute; width: 100%; text-align: center;
                            text-indent: 0; }
.dd-item > button[data-action="collapse"]:before { content: \'-\'; }

.dd-placeholder,
.dd-empty { margin: 5px 0; padding: 0; min-height: 30px; background: #f2fbff; border: 1px dashed #b6bcbf;
            box-sizing: border-box; -moz-box-sizing: border-box; display: block; }
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
.dd-content { display: block; min-height: 30px; margin: 5px 0; padding: 5px 10px 5px 40px; color: #333;
                text-decoration: none; font-weight: bold; border: 1px solid #ccc;
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

.dd-handle { position: absolute; margin: 0; left: 0; top: 0; cursor: pointer; width: 30px; text-indent: 100%;
                white-space: nowrap; overflow: hidden;
  border: 1px solid #aaa;
  background: #ddd;
  background: -webkit-linear-gradient(top, #ddd 0%, #bbb 100%);
  background:    -moz-linear-gradient(top, #ddd 0%, #bbb 100%);
  background:         linear-gradient(top, #ddd 0%, #bbb 100%);
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
  height: 100%;
}
.dd-handle:before { content: \'≡\'; display: block; position: absolute; left: 0; top: 3px; width: 100%;
                    text-align: center; text-indent: 0; color: #fff; font-size: 20px; font-weight: normal; }
.dd-handle:hover { background: #ddd; }
'
                );
                Nestable::$css_rendered = true;
            }
        }

        parent::preRender($form);
    }
}

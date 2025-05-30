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
   ####                     BASE                        ####
   ######################################################### */

namespace Degami\PHPFormsApi\Abstracts\Base;

use Degami\Basics\Traits\ToolsTrait as BasicToolsTrait;
use Degami\PHPFormsApi\Traits\Tools;
use Degami\PHPFormsApi\Form;
use Degami\PHPFormsApi\Accessories\NotificationsBag;
use Degami\Basics\Html\BaseElement;

/**
 * Base element class
 * every form element classes inherits from this class
 *
 * @abstract
 */
abstract class Element extends BaseElement
{
    use BasicToolsTrait, Tools;

    /**
     * Element name
     *
     * @var string
     */
    protected $name;

    /**
     * Element parent
     *
     * @var Element subclass
     */
    protected $parent;

    /**
     * Element weight
     *
     * @var integer
     */
    protected $weight;

    /**
     * Element container tag
     *
     * @var string
     */
    protected $container_tag;

    /**
     * Element container html class
     *
     * @var string
     */
    protected $container_class;

    /**
     * Element label class
     *
     * @var string
     */
    protected $label_class;

    /**
     * Element container inherits classes
     *
     * @var boolean
     */
    protected $container_inherits_classes;

    /**
     * Element errors array
     *
     * @var object
     */
    protected $notifications;

    /**
     * Element js array
     *
     * @var array
     */
    protected $js;

    /**
     * Element css array
     *
     * @var array
     */
    protected $css;

    /**
     * Element prefix
     *
     * @var string
     */
    protected $prefix;

    /**
     * Element suffix
     *
     * @var string
     */
    protected $suffix;

    /**
     * Element build options
     *
     * @var null
     */
    protected $build_options;

    /**
     * Element no translation flag. if true form::translate_string won't be applied
     *
     * @var FALSE
     */
    protected $no_translation;

    /**
     * Class constructor
     * @param array $options constructor options
     * @param string $name element name
     */
    public function __construct($options = [], $name = null)
    {
        if ($options == null) {
            $options = [];
        }

        $this->name = $name;
        $this->parent = null;
        $this->weight = 0;
        $this->container_tag = FORMS_DEFAULT_FIELD_CONTAINER_TAG;
        $this->container_class = FORMS_DEFAULT_FIELD_CONTAINER_CLASS;
        $this->label_class = FORMS_DEFAULT_FIELD_LABEL_CLASS;
        $this->container_inherits_classes = false;
        $this->notifications = new NotificationsBag([ 'error' => [], 'highlight'=> [] ]);
        $this->js = [];
        $this->css = [];
        $this->prefix = '';
        $this->suffix = '';
        $this->build_options = $options;
        $this->no_translation = false;

        $this->setClassProperties($options);
    }

    /**
     * Returns initially build options
     *
     * @return array build_options
     */
    public function getBuildOptions(): ?array
    {
        return $this->build_options;
    }

    /**
     * Set name
     *
     * @param string $name element name
     *
     * @return Element
     */
    public function setName(string $name): Element
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string element name
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set parent
     *
     * @param Element $parent element parent
     *
     * @return Element
     */
    public function setParent(Element $parent): Element
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return Element element parent
     */
    public function getParent(): ?Element
    {
        return $this->parent;
    }

    /**
     * Get weight
     *
     * @return int element weight
     */
    public function getWeight(): int
    {
        return $this->weight;
    }

    /**
     * Add error
     *
     * @param string $error_string error string
     * @param string $validate_function_name validation function name
     *
     * @return Element
     */
    public function addError(string $error_string, string $validate_function_name): Element
    {
        $this->notifications['error'][$validate_function_name] = $error_string;
        return $this;
    }

    /**
     * Get defined errors
     *
     * @return array errors
     */
    public function getErrors(): array
    {
        return $this->notifications['error']->toArray();
    }

    /**
     * Check if element has errors
     *
     * @return boolean there are errors
     */
    public function hasErrors(): bool
    {
        return count($this->getErrors()) > 0;
    }

    /**
     * Set element errors
     *
     * @param array $errors errors array
     *
     * @return Element
     */
    public function setErrors(array $errors): Element
    {
        $this->notifications['error'] = $errors;

        return $this;
    }

    /**
     * Add highlight
     *
     * @param string $highlight_string highlight string
     *
     * @return Element
     */
    public function addHighlight(string $highlight_string): Element
    {
        $this->notifications['highlight'][] = $highlight_string;

        return $this;
    }

    /**
     * Get defined highlights
     *
     * @return array errors
     */
    public function getHighlights(): array
    {
        return $this->notifications['highlight']->toArray();
    }

    /**
     * Check if element has highlights
     *
     * @return boolean there are highlights
     */
    public function hasHighlights(): bool
    {
        return count($this->getHighlights()) > 0;
    }

    /**
     * Set element highlights
     *
     * @param array $highlights highlights array
     *
     * @return Element
     */
    public function setHighlights(array $highlights): Element
    {
        $this->notifications['highlight'] = $highlights;

        return $this;
    }

    /**
     * Add js to element
     *
     * @param string / array $js    javascript to add
     * @param boolean        $as_is no "minification"
     *
     * @return Element
     */
    public function addJs($js, $as_is = false): Element
    {
        if (getenv('DEBUG') == true) {
            $as_is = true;
        }

        if (!$as_is) {
            if (is_array($js)) {
                $js = array_filter(array_map(['minify_js', $this], $js));
            } elseif (is_string($js) && trim($js) != '') {
                $js = $this->minifyJs($js);
            }
        }

        if (is_array($js)) {
            $this->js = array_merge($js, $this->js);
        } elseif (is_string($js) && trim($js) != '') {
            $this->js[] = $js;
        }

        return $this;
    }

    /**
     * minify js string
     *
     * @param  string $js javascript minify
     * @return string
     */
    public function minifyJs($js): string
    {
        if (is_string($js) && trim($js) != '') {
            $js = trim(preg_replace("/\s+/", " ", str_replace("\n", "", "". $js)));
        }

        return $js;
    }

    /**
     * Get the element's js array
     *
     * @return array element's js array
     */
    public function &getJs(): array
    {
        if ($this instanceof FieldsContainer || $this instanceof Form) {
            $js = array_filter(array_map('trim', $this->js));
            $fields = $this->getFields();
            if ($this instanceof Form) {
                $fields = $this->getFields($this->getCurrentStep());
            }
            foreach ($fields as $field) {
                /** @var Field $field */
                $js = array_merge($js, $field->getJs());
            }
            return $js;
        }
        return $this->js;
    }


    /**
     * Add css to element
     *
     * @param  string / array $css css to add
     * @return Element
     */
    public function addCss($css): Element
    {
        if (is_array($css)) {
            $css = array_filter(array_map('trim', $css));
            $this->css = array_merge($css, $this->css);
        } elseif (is_string($css) && trim($css) != '') {
            $this->css[] = trim($css);
        }

        return $this;
    }

    /**
     * Get the element's css array
     *
     * @return array element's css array
     */
    public function &getCss(): array
    {
        if ($this instanceof FieldsContainer || $this instanceof Form) {
            $css = array_filter(array_map('trim', $this->css));
            foreach ($this->getFields() as $field) {
                /** @var Field $field */
                $css = array_merge($css, $field->getCss());
            }
            return $css;
        }
        return $this->css;
    }

    /**
     * Get element prefix
     *
     * @return string element prefix
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * Set element prefix
     *
     * @param string $prefix element prefix
     * @return Element
     */
    public function setPrefix(string $prefix): Element
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Get element suffix
     *
     * @return string element suffix
     */
    public function getSuffix(): string
    {
        return $this->suffix;
    }

    /**
     * Set element suffix
     *
     * @param string $suffix element suffix
     * @return Element
     */
    public function setSuffix(string $suffix): Element
    {
        $this->suffix = $suffix;

        return $this;
    }

    /**
     * Get element container_tag
     *
     * @return string element container_tag
     */
    public function getContainerTag(): string
    {
        return $this->container_tag;
    }

    /**
     * Set element container_tag
     *
     * @param string $container_tag element container_tag
     *
     * @return Element
     */
    public function setContainerTag(string $container_tag): Element
    {
        $this->container_tag = $container_tag;

        return $this;
    }

    /**
     * Get element container_class
     *
     * @return string element container_class
     */
    public function getContainerClass(): string
    {
        return $this->container_class;
    }

    /**
     * Set element container_class
     *
     * @param string $container_class element container_class
     *
     * @return Element
     */
    public function setContainerClass(string $container_class): Element
    {
        $this->container_class = $container_class;

        return $this;
    }

    /**
     * Get element html prefix
     *
     * @return string html for the element prefix
     */
    public function getElementPrefix(): string
    {
        if (!empty($this->container_tag)) {
            if (preg_match("/<\/?(.*?)\s.*?(class=\"(.*?)\")?.*?>/i", $this->container_tag, $matches)) {
                // if a <tag> is contained try to get tag and class
                $this->container_tag = $matches[1];
                $this->container_class = (
                    !empty($this->container_class) ? $this->container_class : ''
                  ) . (
                    !empty($matches[3]) ? ' '.$matches[3] : ''
                  );
            }

            $class = $this->container_class;
            if ($this->container_inherits_classes
                && isset($this->attributes['class'])
                && !empty($this->attributes['class'])
            ) {
                $class .= ' '.$this->attributes['class'].'-container';
            } else {
                if (method_exists($this, 'getType')) {
                    $class .= ' '.$this->getType().'-container';
                }
            }
            if ($this->hasErrors()) {
                $class .= ' has-errors';
            }
            $class = trim($class);
            return "<{$this->container_tag} class=\"{$class}\">";
        }
        return '';
    }

    /**
     * Get element html suffix
     *
     * @return string html for the element suffix
     */
    public function getElementSuffix(): string
    {
        if (!empty($this->container_tag)) {
            return "</{$this->container_tag}>";
        }
        return '';
    }

    /**
     * search field by field html id
     *
     * @param Element $container container to search into
     * @param string $field_id field id
     * @return Field|null                      Field object or null if not found
     */
    protected static function searchFieldById(Element $container, string $field_id)
    {
        /** @var Field $container */
        if ($container instanceof FieldsContainer || $container instanceof Form) {
            $fields = ($container instanceof Form) ?
                        $container->getFields($container->getCurrentStep()) :
                        $container->getFields();
            foreach ($fields as $key => $field) {
                /** @var Field $field */
                if ($field->getHtmlId() == $field_id) {
                    return $field;
                } elseif ($field instanceof FieldsContainer) {
                    $out = Element::searchFieldById($field, $field_id);
                    if ($out != null) {
                        return $out;
                    }
                }
            }
        } elseif ($container->getHtmlId() == $field_id) {
            // not a container
            return $container;
        }
        return null;
    }

    public function getData(): array
    {
        return get_object_vars($this);
    }
}

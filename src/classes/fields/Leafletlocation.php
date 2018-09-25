<?php
/**
 * PHP FORMS API
 *
 * @package degami/php-forms-api
 */
/* #########################################################
   ####                    FIELDS                       ####
   ######################################################### */

namespace Degami\PHPFormsApi\Fields;

use Degami\PHPFormsApi\Form;

/**
 * the google maps geolocation field class
 */
class Leafletlocation extends Geolocation
{

    /**
     * MapBox accessToken
     *
     * @see https://www.mapbox.com/about/maps/
     * @var string
     */
    protected $accessToken = null;

    /**
     * zoom
     *
     * @var integer
     */
    protected $zoom = 8;

    /**
     * map width
     *
     * @var string
     */
    protected $mapwidth = '100%';

    /**
     * map height
     *
     * @var string
     */
    protected $mapheight = '500px';

    /**
     * marker title
     *
     * @var null
     */
    protected $markertitle = null;

    /**
     * map type - one of:
     * mapbox.streets
     * mapbox.light
     * mapbox.dark
     * mapbox.satellite
     * mapbox.streets-satellite
     * mapbox.wheatpaste
     * mapbox.streets-basic
     * mapbox.comic
     * mapbox.outdoors
     * mapbox.run-bike-hike
     * mapbox.pencil
     * mapbox.pirates
     * mapbox.emerald
     * mapbox.high-contrast
     *
     * @var string
     */
    protected $maptype = 'mapbox.streets';

    /**
     * input type where latitude and longitude are stored (hidden / textfield)
     *
     * @var string
     */
    protected $lat_lon_type = 'hidden';

    /**
     * class constructor
     *
     * @param array  $options build options
     * @param string $name    field name
     */
    public function __construct($options = [], $name = null)
    {
        parent::__construct($options, $name);
        $defaults = isset($options['default_value']) ? $options['default_value'] : ['latitude' => 0, 'longitude' => 0];

        unset($options['title']);
        unset($options['prefix']);
        unset($options['suffix']);
        $options['container_tag'] = '';

        $opt = $options;
        $opt['type'] = 'hidden';
        $opt['attributes']['class'] = 'latitude';
        if ($this->lat_lon_type == 'textfield') {
            $opt['type'] = 'textfield';
        }
        $opt['default_value'] = (is_array($defaults) && isset($defaults['latitude'])) ? $defaults['latitude'] : 0;
        if ($this->lat_lon_type == 'textfield') {
            $opt['suffix'] = $this->getText('latitude').' ';
        }
        if ($this->lat_lon_type == 'textfield') {
            $this->latitude = new Textfield($opt, $name.'_latitude');
        } else {
            $this->latitude = new Hidden($opt, $name.'_latitude');
        }

        $opt = $options;
        $opt['type'] = 'hidden';
        $opt['attributes']['class'] = 'longitude';
        if ($this->lat_lon_type == 'textfield') {
            $opt['type'] = 'textfield';
        }
        $opt['default_value'] = (is_array($defaults) && isset($defaults['longitude'])) ? $defaults['longitude'] : 0;
        if ($this->lat_lon_type == 'textfield') {
            $opt['suffix'] = $this->getText('longitude').' ';
        }
        if ($this->lat_lon_type == 'textfield') {
            $this->longitude = new Textfield($opt, $name.'_longitude');
        } else {
            $this->longitude = new Hidden($opt, $name.'_longitude');
        }
    }


    /**
     * {@inheritdoc} . it simply calls the sub elements preprocess
     *
     * @param string $process_type preprocess type
     */
    public function preprocess($process_type = "preprocess")
    {
        parent::preprocess($process_type);
    }


    /**
     * {@inheritdoc} . it simply calls the sub elements process
     *
     * @param array $values value to set
     */
    public function process($values)
    {
        parent::process($values);
    }

    /**
     * return field value
     *
     * @return array field value
     */
    public function values()
    {
        $out = parent::values();
        return $out;
    }

    /**
     * {@inheritdoc}
     *
     * @param Form $form form object
     */
    public function preRender(Form $form)
    {
        if ($this->pre_rendered == true) {
            return;
        }
        $id = $this->getHtmlId();

        $this->addCss("#{$form->getId()} #{$id}-map {width: {$this->mapwidth}; height: {$this->mapheight}; }");
        $this->addJs("var {$id}_latlng = {lat: ".$this->latitude->values().", lng: ".$this->longitude->values()."};
          var {$id}_map = L.map('{$id}-map').setView([{$id}_latlng.lat,{$id}_latlng.lng],{$this->zoom});
          L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token={accessToken}', {
              attribution: 
                'Map data &copy; <a href=\"https://www.openstreetmap.org/\">OpenStreetMap</a> contributors,'+ 
                '<a href=\"https://creativecommons.org/licenses/by-sa/2.0/\">CC-BY-SA</a>,'+
                ' Imagery © <a href=\"https://www.mapbox.com/\">Mapbox</a>',
              maxZoom: 18,
              id: '{$this->maptype}',
              accessToken: '{$this->accessToken}'
          }).addTo({$id}_map);
    
          var {$id}_marker = L.marker([{$id}_latlng.lat, {$id}_latlng.lng],{
            draggable: true
          }).addTo({$id}_map);
    
          {$id}_marker.on('dragend', function(e){
            {$id}_map.panTo( {$id}_marker.getLatLng() );
            \$('input[name=\"{$id}_latitude\"]','#{$id}').val( {$id}_marker.getLatLng().lat );
            \$('input[name=\"{$id}_longitude\"]','#{$id}').val( {$id}_marker.getLatLng().lng );
          });
    
          \$.data( \$('#{$id}-map')[0] , 'map_obj', {$id}_map);
          \$.data( \$('#{$id}-map')[0] , 'marker_obj', {$id}_marker);    
        ");

        if ($this->lat_lon_type == 'textfield') {
            $this->addJs("\$('input[name=\"{$id}_latitude\"],input[name=\"{$id}_longitude\"]','#{$id}')
            .change(function(evt){
                var map = \$.data( \$('#{$id}-map')[0] , 'map_obj');
                var marker = \$.data( \$('#{$id}-map')[0] , 'marker_obj');
                var lat = \$('input[name=\"{$id}_latitude\"]','#{$id}').val();
                var lng = \$('input[name=\"{$id}_longitude\"]','#{$id}').val();
    
                map.panTo(L.latLng(lat, lng));
                marker.setLatLng(L.latLng(lat, lng));
            });");
        }

        parent::preRender($form);
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
        $attributes = $this->getAttributes();

        $this->tag = 'div';
        $output = "<{$this->tag} id=\"{$id}\"{$attributes}>\n";

        $required = ($this->validate->hasValue('required')) ? '<span class="required">*</span>' : '';
        $requiredafter = $requiredbefore = $required;
        if ($this->required_position == 'before') {
            $requiredafter = '';
            $requiredbefore = $requiredbefore.' ';
        } else {
            $requiredbefore = '';
            $requiredafter = ' '.$requiredafter;
        }

        if (!empty($this->title)) {
            if ($this->tooltip == false) {
                $this->label_class .= " label-" .$this->getElementClassName();
                $this->label_class = trim($this->label_class);
                $label_class = (!empty($this->label_class)) ? " class=\"{$this->label_class}\"" : "";
                $output .= "<label for=\"{$id}\" {$label_class}>{$requiredbefore}".
                            $this->getText($this->title).
                            "{$requiredafter}</label>\n";
            } else {
                if (!in_array('title', array_keys($this->attributes))) {
                    $this->attributes['title'] = strip_tags($this->getText($this->title).$required);
                }

                $id = $this->getHtmlId();
                $form->addJs("\$('#{$id}','#{$form->getId()}').tooltip();");
            }
        }

        $mapattributes = ' class="leafletmap"';
        $output .= "<div id=\"{$id}-map\" {$mapattributes}></div>\n";

        $output .= $this->latitude->render($form);
        $output .= $this->longitude->render($form);


        $output .= "</{$this->tag}>\n";
        return $output;
    }
}

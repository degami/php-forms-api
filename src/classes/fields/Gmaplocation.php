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

/**
 * The google maps geolocation field class
 */
class Gmaplocation extends Geolocation
{

    /**
     * "current location" button
     *
     * @var Button
     */
    protected $current_location_btn;


    /**
     * zoom
     *
     * @var integer
     */
    protected $zoom = 8;

    /**
     * scrollwheel
     *
     * @var boolean
     */
    protected $scrollwheel = false;

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
     * google.maps.MapTypeId.HYBRID,
     * google.maps.MapTypeId.ROADMAP,
     * google.maps.MapTypeId.SATELLITE,
     * google.maps.MapTypeId.TERRAIN
     *
     * @var string
     */
    protected $maptype = 'google.maps.MapTypeId.ROADMAP';

    /**
     * enable geocode box
     *
     * @var boolean
     */
    protected $with_geocode = false;

    /**
     * enable current location button
     *
     * @var boolean
     */
    protected $with_current_location = false;

    /**
     * input type where latitude and longitude are stored (hidden / textfield)
     *
     * @var string
     */
    protected $lat_lon_type = 'hidden';

    /**
     * textfield subelement for geocode box
     *
     * @var null
     */
    protected $geocode_box = null;

    /**
     * textarea subelement for reverse geocoding informations
     *
     * @var null
     */
    protected $reverse_geocode_box = null;

    /**
     * "show map" flag
     *
     * @var boolean
     */
    protected $with_map = true;

    /**
     * enable reverse geociding information box
     *
     * @var boolean
     */
    protected $with_reverse = false;

    /**
     * Class constructor
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

        if ($this->with_geocode == true) {
            $opt = $options;
            $opt['type'] = 'textfield';
            $opt['size'] = 50;
            $opt['attributes']['class'] = 'geocode';
            $opt['default_value'] = (is_array($defaults) &&
                                    isset($defaults['geocodebox'])) ?
                                    $defaults['geocodebox'] :
                                    '';
            $this->geocode_box = new Textfield($opt, $name.'_geocodebox');
        }

        if ($this->with_reverse == true) {
            $opt = $options;
            $opt['type'] = 'textarea';
            $opt['attributes']['class'] = 'reverse';
            $opt['default_value'] = (is_array($defaults) &&
                                    isset($defaults['reverse_geocodebox'])) ?
                                    $defaults['reverse_geocodebox'] :
                                    '';
            $this->reverse_geocode_box = new Textarea($opt, $name.'_reverse_geocodebox');
        }

        if ($this->with_current_location == true) {
            $opt = $options;
            $opt['type'] = 'button';
            $opt['size'] = 50;
            $opt['attributes']['class'] = 'current_location';
            $opt['default_value'] = $this->getText('Current Location');
            $this->current_location_btn = new Button($opt, $name.'_current_location_btn');
        }
    }

    /**
     * {@inheritdoc} . it simply calls the sub elements preprocess
     *
     * @param string $process_type preprocess type
     */
    public function preProcess($process_type = "preprocess")
    {
        parent::preprocess($process_type);
        if ($this->with_geocode == true) {
            $this->geocode_box->preProcess($process_type);
        }
        if ($this->with_reverse == true) {
            $this->reverse_geocode_box->preProcess($process_type);
        }
    }


    /**
     * {@inheritdoc} . it simply calls the sub elements process
     *
     * @param array $values value to set
     */
    public function processValue($values)
    {
        parent::processValue($values);
        if ($this->with_geocode == true) {
            $this->geocode_box->processValue($values[$this->getName().'_geocodebox']);
        }
        if ($this->with_reverse == true) {
            $this->reverse_geocode_box->processValue($values[$this->getName().'_reverse_geocodebox']);
        }
    }

    /**
     * Return field value
     *
     * @return array field value
     */
    public function getValues()
    {
        $out = parent::getValues();
        if ($this->with_geocode == true) {
            $out += [ 'geocodebox' => $this->geocode_box->getValues() ];
        }
        if ($this->with_reverse == true) {
            $out += [ 'reverse_geocodebox' => $this->reverse_geocode_box->getValues() ];
        }
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

        if ($this->with_geocode == true) {
            $update_map_func = "";
            if ($this->with_map == true) {
                $update_map_func = "
        var map = \$.data( \$('#{$id}-map')[0] , 'map_obj');
        var marker = \$.data( \$('#{$id}-map')[0] , 'marker_obj');
        marker.setPosition( new google.maps.LatLng( lat, lng ) );
        map.panTo( new google.maps.LatLng( lat, lng ) );
        ";
            }

            $this->addJs(
                "
          var {$id}_api_endpoint = 'https://maps.googleapis.com/maps/api/geocode/json?address=';
          \$('#{$id}_geocodebox').autocomplete({
            source: function (request, response) {
                jQuery.get({$id}_api_endpoint+\$('#{$id}_geocodebox').val(), {
                    query: request.term
                }, function (data) {
                  response($.map( data.results, function( item ) {
                      return {
                          label: item.formatted_address,
                          id: item.geometry.location.lat+'|'+item.geometry.location.lng
                      }
                  }));
                });
            },
            minLength: 5,
            select: function( event, ui ) {
              var tmp = ui.item.id.split('|');
              var lat = tmp[0];
              var lng = tmp[1];

              \$('input[name=\"{$id}_latitude\"]','#{$id}').val( lat );
              \$('input[name=\"{$id}_longitude\"]','#{$id}').val( lng );
              ".(($this->with_reverse == true) ? "\$('#{$id}').trigger('lat_lon_updated');":"")."

              {$update_map_func}

            }
          });
      "
            );
        }

        if ($this->with_map == true) {
            $this->addCss("#{$form->getId()} #{$id}-map {width: {$this->mapwidth}; height: {$this->mapheight}; }");
            $this->addJs(
                "
                var {$id}_latlng = {lat: ".$this->latitude->getValues().", lng: ".$this->longitude->getValues()."};

                var {$id}_map = new google.maps.Map(document.getElementById('{$id}-map'), {
                  center: {$id}_latlng,
                  mapTypeId: {$this->maptype},
                  scrollwheel: ".($this->scrollwheel ? 'true' : 'false').",
                  zoom: {$this->zoom}
                });
                var {$id}_marker = new google.maps.Marker({
                  map: {$id}_map,
                  draggable: true,
                  animation: google.maps.Animation.DROP,
                  position: {$id}_latlng,
                  title: '".(($this->markertitle == null) ?
                            "lat: ".$this->latitude->getValues().", lng: ".$this->longitude->getValues() :
                            $this->markertitle)."'
                });
                \$.data( \$('#{$id}-map')[0] , 'map_obj', {$id}_map);
                \$.data( \$('#{$id}-map')[0] , 'marker_obj', {$id}_marker);

                google.maps.event.addListener({$id}_marker, 'dragend', function() {
                  var mapdiv = {$id}_marker.map.getDiv();
                  \$('input[name=\"{$id}_latitude\"]','#'+\$(mapdiv).parent().
                  attr('id')).val( {$id}_marker.getPosition().lat() );
                  \$('input[name=\"{$id}_longitude\"]','#'+\$(mapdiv).parent().
                  attr('id')).val( {$id}_marker.getPosition().lng() );
                  ".(($this->with_reverse == true) ? "\$('#{$id}').trigger('lat_lon_updated');":"")."
                });"
            );

            if ($this->lat_lon_type == 'textfield') {
                $this->addJs(
                    "\$('input[name=\"{$id}_latitude\"],input[name=\"{$id}_longitude\"]','#{$id}')
                .change(function(evt){
                  var map = \$.data( \$('#{$id}-map')[0] , 'map_obj');
                  var marker = \$.data( \$('#{$id}-map')[0] , 'marker_obj');
                  var lat = \$('input[name=\"{$id}_latitude\"]','#{$id}').val();
                  var lng = \$('input[name=\"{$id}_longitude\"]','#{$id}').val();
                  marker.setPosition( new google.maps.LatLng( lat, lng ) );
                  map.panTo( new google.maps.LatLng( lat, lng ) );
                });"
                );
            }
        }

        if ($this->with_reverse == true) {
            $this->addJs(
                "var {$id}_geocoder = new google.maps.Geocoder;
                \$('#{$id}').bind('lat_lon_updated',function(evt){
                  var latlng = {
                    lat: parseFloat( \$('input[name=\"{$id}_latitude\"]','#{$id}').val() ),
                    lng: parseFloat( \$('input[name=\"{$id}_longitude\"]','#{$id}').val() )
                  };
                  {$id}_geocoder.geocode({'location': latlng}, function(results, status) {
                    if (status === 'OK') {
                      \$('#{$id}_reverse_geocodebox').val( JSON.stringify(results) );
                    } else {
                      \$('#{$id}_reverse_geocodebox').val('Geocoder failed due to: ' + status);
                    }
                  });
                });"
            );

            if ($this->lat_lon_type == 'textfield') {
                $this->addJs(
                    "\$('input[name=\"{$id}_latitude\"],input[name=\"{$id}_longitude\"]','#{$id}')
                .change(function(evt){
                    \$('#{$id}').trigger('lat_lon_updated');
                });"
                );
            }
        }

        if ($this->with_current_location == true) {
            $update_map_func = "";
            if ($this->with_map == true) {
                $update_map_func = "
                    var map = \$.data( \$('#{$id}-map')[0] , 'map_obj');
                    var marker = \$.data( \$('#{$id}-map')[0] , 'marker_obj');
                    marker.setPosition( new google.maps.LatLng( lat, lng ) );
                    map.panTo( new google.maps.LatLng( lat, lng ) );
                ";
            }
            $this->addJs(
                "\$('button.current_location','#{$id}')
            .click(function(evt){
              evt.preventDefault();
              var lat = \$('input[name=\"{$id}_latitude\"]','#{$id}').val();
              var lng = \$('input[name=\"{$id}_longitude\"]','#{$id}').val();

              if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                  lat = position.coords.latitude;
                  lng = position.coords.longitude;
                  \$('input[name=\"{$id}_latitude\"]','#{$id}').val(lat);
                  \$('input[name=\"{$id}_longitude\"]','#{$id}').val(lng);
                  ".(($this->with_reverse == true) ? "\$('#{$id}').trigger('lat_lon_updated');":"")."

                  {$update_map_func}
                }, function() {
                  /*handleLocationError();*/
                });
              }
            });"
            );
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
        $this->tag = 'div';

        $required = ($this->validate->hasValue('required')) ? '<span class="required">*</span>' : '';
        $requiredafter = $requiredbefore = $required;
        if ($this->required_position == 'before') {
            $requiredafter = '';
            $requiredbefore = $requiredbefore.' ';
        } else {
            $requiredbefore = '';
            $requiredafter = ' '.$requiredafter;
        }

        if (!empty($this->title) && $this->tooltip == true && !in_array('title', array_keys($this->attributes))) {
            $this->attributes['title'] = strip_tags($this->getText($this->title).$required);
        }

        $tag = new TagElement(
            [
                'tag' => $this->tag,
                'id' => $id,
                'attributes' => $this->attributes,
            ]
        );

        if (!empty($this->title)) {
            if ($this->tooltip == false) {
                $this->label_class .= " label-" .$this->getElementClassName();
                $this->label_class = trim($this->label_class);
                $tag_label = new TagElement(
                    [
                        'tag' => 'label',
                        'attributes' => [
                          'for' => $id,
                          'class' => $this->label_class,
                          'text' => $requiredbefore
                        ],
                    ]
                );
                $tag_label->addChild($this->getText($this->title));
                $tag_label->addChild($requiredafter);
                $tag->addChild($tag_label);
            } else {
                $id = $this->getHtmlId();
                $form->addJs("\$('#{$id}','#{$form->getId()}').tooltip();");
            }
        }

        if ($this->with_geocode == true) {
            $tag->addChild($this->geocode_box->renderHTML($form));
        }

        if ($this->with_map == true) {
            $tag->addChild(new TagElement(
                [
                    'tag' => 'div',
                    'id' => "{$id}-map",
                    'attributes' => ['class' => 'gmap'],
                ]
            ));
        }

        $tag->addChild($this->latitude->renderHTML($form));
        $tag->addChild($this->longitude->renderHTML($form));

        if ($this->with_current_location == true) {
            $tag->addChild($this->current_location_btn->renderHTML($form));
        }

        if ($this->with_reverse == true) {
            $tag->addChild($this->reverse_geocode_box->renderHTML($form));
        }

        return $tag;
    }
}

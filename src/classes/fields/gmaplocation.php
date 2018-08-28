<?php
/**
 * PHP FORMS API
 * @package degami/php-forms-api
 */
/* #########################################################
   ####                    FIELDS                       ####
   ######################################################### */

namespace Degami\PHPFormsApi\Fields;

use Degami\PHPFormsApi\form;
use Degami\PHPFormsApi\Abstracts\Base\field;

/**
 * the google maps geolocation field class
 */
class gmaplocation extends geolocation {

  /**
   * "current location" button
   * @var button
   */
  protected $current_location_btn;


  /**
   * zoom
   * @var integer
   */
  protected $zoom = 8;

  /**
   * scrollwheel
   * @var boolean
   */
  protected $scrollwheel = FALSE;

  /**
   * map width
   * @var string
   */
  protected $mapwidth = '100%';

  /**
   * map height
   * @var string
   */
  protected $mapheight = '500px';

  /**
   * marker title
   * @var null
   */
  protected $markertitle = NULL;

  /**
   * map type - one of:
   * google.maps.MapTypeId.HYBRID,
   * google.maps.MapTypeId.ROADMAP,
   * google.maps.MapTypeId.SATELLITE,
   * google.maps.MapTypeId.TERRAIN
   * @var string
   */
  protected $maptype = 'google.maps.MapTypeId.ROADMAP';

  /**
   * enable geocode box
   * @var boolean
   */
  protected $with_geocode = FALSE;

  /**
   * enable current location button
   * @var boolean
   */
  protected $with_current_location = FALSE;

  /**
   * input type where latitude and longitude are stored (hidden / textfield)
   * @var string
   */
  protected $lat_lon_type = 'hidden';

  /**
   * textfield subelement for geocode box
   * @var null
   */
  protected $geocode_box = NULL;

  /**
   * textarea subelement for reverse geocoding informations
   * @var null
   */
  protected $reverse_geocode_box = NULL;

  /**
   * "show map" flag
   * @var boolean
   */
  protected $with_map = TRUE;

  /**
   * enable reverse geociding information box
   * @var boolean
   */
  protected $with_reverse = FALSE;

  /**
   * class constructor
   * @param array  $options build options
   * @param string $name    field name
   */
  public function __construct($options = [], $name = NULL) {
    parent::__construct($options,$name);
    $defaults = isset($options['default_value']) ? $options['default_value'] : ['latitude' => 0, 'longitude' => 0];

    unset($options['title']);
    unset($options['prefix']);
    unset($options['suffix']);
    $options['container_tag'] = '';

    $opt = $options;
    $opt['type'] = 'hidden';
    $opt['attributes']['class'] = 'latitude';
    if($this->lat_lon_type == 'textfield') $opt['type'] = 'textfield';
    $opt['default_value'] = (is_array($defaults) && isset($defaults['latitude'])) ? $defaults['latitude'] : 0;
    if($this->lat_lon_type == 'textfield') $opt['suffix'] = $this->get_text('latitude').' ';
    if($this->lat_lon_type == 'textfield') $this->latitude = new textfield($opt,$name.'_latitude');
    else $this->latitude = new hidden($opt,$name.'_latitude');

    $opt = $options;
    $opt['type'] = 'hidden';
    $opt['attributes']['class'] = 'longitude';
    if($this->lat_lon_type == 'textfield') $opt['type'] = 'textfield';
    $opt['default_value'] = (is_array($defaults) && isset($defaults['longitude'])) ? $defaults['longitude'] : 0;
    if($this->lat_lon_type == 'textfield') $opt['suffix'] = $this->get_text('longitude').' ';
    if($this->lat_lon_type == 'textfield') $this->longitude = new textfield($opt,$name.'_longitude');
    else $this->longitude = new hidden($opt,$name.'_longitude');

    if($this->with_geocode == TRUE){
      $opt = $options;
      $opt['type'] = 'textfield';
      $opt['size'] = 50;
      $opt['attributes']['class'] = 'geocode';
      $opt['default_value'] = (is_array($defaults) && isset($defaults['geocodebox'])) ? $defaults['geocodebox'] : '';
      $this->geocode_box = new textfield($opt,$name.'_geocodebox');
    }

    if( $this->with_reverse == TRUE ){
      $opt = $options;
      $opt['type'] = 'textarea';
      $opt['attributes']['class'] = 'reverse';
      $opt['default_value'] = (is_array($defaults) && isset($defaults['reverse_geocodebox'])) ? $defaults['reverse_geocodebox'] : '';
      $this->reverse_geocode_box = new textarea($opt,$name.'_reverse_geocodebox');
    }

    if($this->with_current_location == TRUE){
      $opt = $options;
      $opt['type'] = 'button';
      $opt['size'] = 50;
      $opt['attributes']['class'] = 'current_location';
      $opt['default_value'] = $this->get_text('Current Location');
      $this->current_location_btn = new button($opt,$name.'_current_location_btn');
    }
  }


  /**
   * preprocess hook . it simply calls the sub elements preprocess
   * @param  string $process_type preprocess type
   */
  public function preprocess($process_type = "preprocess") {
    parent::preprocess($process_type);
    if($this->with_geocode == TRUE){
      $this->geocode_box->preprocess($process_type);
    }
    if($this->with_reverse == TRUE){
      $this->reverse_geocode_box->preprocess($process_type);
    }
  }


  /**
   * process hook . it simply calls the sub elements process
   * @param  array $values value to set
   */
  public function process($values) {
    parent::process($values);
    if($this->with_geocode == TRUE){
      $this->geocode_box->process($values[$this->get_name().'_geocodebox']);
    }
    if($this->with_reverse == TRUE){
      $this->reverse_geocode_box->process($values[$this->get_name().'_reverse_geocodebox']);
    }
  }

  /**
   * return field value
   * @return array field value
   */
  public function values() {
    $out = parent::values();
    if($this->with_geocode == TRUE){
      $out += [ 'geocodebox' => $this->geocode_box->values() ];
    }
    if($this->with_reverse == TRUE){
      $out += [ 'reverse_geocodebox' => $this->reverse_geocode_box->values() ];
    }
    return $out;
  }

  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();

    if($this->with_geocode == TRUE){
      $update_map_func = "";
      if($this->with_map == TRUE){
        $update_map_func = "
        var map = \$.data( \$('#{$id}-map')[0] , 'map_obj');
        var marker = \$.data( \$('#{$id}-map')[0] , 'marker_obj');
        marker.setPosition( new google.maps.LatLng( lat, lng ) );
        map.panTo( new google.maps.LatLng( lat, lng ) );
        ";
      }

      $this->add_js("
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
              ".(($this->with_reverse == TRUE) ? "\$('#{$id}').trigger('lat_lon_updated');":"")."

              {$update_map_func}

            }
          });
      ");
    }

    if($this->with_map == TRUE){
      $this->add_css("#{$form->get_id()} #{$id}-map {width: {$this->mapwidth}; height: {$this->mapheight}; }");
      $this->add_js("
        var {$id}_latlng = {lat: ".$this->latitude->values().", lng: ".$this->longitude->values()."};

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
          title: '".(($this->markertitle == NULL) ? "lat: ".$this->latitude->values().", lng: ".$this->longitude->values() : $this->markertitle)."'
        });
        \$.data( \$('#{$id}-map')[0] , 'map_obj', {$id}_map);
        \$.data( \$('#{$id}-map')[0] , 'marker_obj', {$id}_marker);

        google.maps.event.addListener({$id}_marker, 'dragend', function() {
          var mapdiv = {$id}_marker.map.getDiv();
          \$('input[name=\"{$id}_latitude\"]','#'+\$(mapdiv).parent().attr('id')).val( {$id}_marker.getPosition().lat() );
          \$('input[name=\"{$id}_longitude\"]','#'+\$(mapdiv).parent().attr('id')).val( {$id}_marker.getPosition().lng() );
          ".(($this->with_reverse == TRUE) ? "\$('#{$id}').trigger('lat_lon_updated');":"")."
        });
      ");

      if($this->lat_lon_type == 'textfield'){
        $this->add_js("
            \$('input[name=\"{$id}_latitude\"],input[name=\"{$id}_longitude\"]','#{$id}').change(function(evt){
              var map = \$.data( \$('#{$id}-map')[0] , 'map_obj');
              var marker = \$.data( \$('#{$id}-map')[0] , 'marker_obj');
              var lat = \$('input[name=\"{$id}_latitude\"]','#{$id}').val();
              var lng = \$('input[name=\"{$id}_longitude\"]','#{$id}').val();
              marker.setPosition( new google.maps.LatLng( lat, lng ) );
              map.panTo( new google.maps.LatLng( lat, lng ) );
            });
        ");
      }

    }

    if( $this->with_reverse == TRUE ){
      $this->add_js("
            var {$id}_geocoder = new google.maps.Geocoder;
            \$('#{$id}').bind('lat_lon_updated',function(evt){
              var latlng = {lat: parseFloat( \$('input[name=\"{$id}_latitude\"]','#{$id}').val() ), lng: parseFloat( \$('input[name=\"{$id}_longitude\"]','#{$id}').val() )};
              {$id}_geocoder.geocode({'location': latlng}, function(results, status) {
                if (status === 'OK') {
                  \$('#{$id}_reverse_geocodebox').val( JSON.stringify(results) );
                } else {
                  \$('#{$id}_reverse_geocodebox').val('Geocoder failed due to: ' + status);
                }
              });
            });
        ");

      if($this->lat_lon_type == 'textfield'){
        $this->add_js("
              \$('input[name=\"{$id}_latitude\"],input[name=\"{$id}_longitude\"]','#{$id}').change(function(evt){
                \$('#{$id}').trigger('lat_lon_updated');
              });
          ");
      }
    }

    if($this->with_current_location == TRUE){
      $update_map_func = "";
      if($this->with_map == TRUE){
        $update_map_func = "
            var map = \$.data( \$('#{$id}-map')[0] , 'map_obj');
            var marker = \$.data( \$('#{$id}-map')[0] , 'marker_obj');
            marker.setPosition( new google.maps.LatLng( lat, lng ) );
            map.panTo( new google.maps.LatLng( lat, lng ) );
          ";
      }
      $this->add_js("
            \$('button.current_location','#{$id}').click(function(evt){
              evt.preventDefault();
              var lat = \$('input[name=\"{$id}_latitude\"]','#{$id}').val();
              var lng = \$('input[name=\"{$id}_longitude\"]','#{$id}').val();

              if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                  lat = position.coords.latitude;
                  lng = position.coords.longitude;
                  \$('input[name=\"{$id}_latitude\"]','#{$id}').val(lat);
                  \$('input[name=\"{$id}_longitude\"]','#{$id}').val(lng);
                  ".(($this->with_reverse == TRUE) ? "\$('#{$id}').trigger('lat_lon_updated');":"")."

                  {$update_map_func}
                }, function() {
                  /*handleLocationError();*/
                });
              }
            });
        ");
    }

    parent::pre_render($form);
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();
    $attributes = $this->get_attributes();

    $this->tag = 'div';
    $output = "<{$this->tag} id=\"{$id}\"{$attributes}>\n";

    $required = ($this->validate->has_value('required')) ? '<span class="required">*</span>' : '';
    $requiredafter = $requiredbefore = $required;
    if($this->required_position == 'before') { $requiredafter = ''; $requiredbefore = $requiredbefore.' '; }
    else { $requiredbefore = ''; $requiredafter = ' '.$requiredafter; }

    if(!empty($this->title)){
      if ( $this->tooltip == FALSE ) {
        $this->label_class .= " label-" .$this->get_element_class_name();
        $this->label_class = trim($this->label_class);
        $label_class = (!empty($this->label_class)) ? " class=\"{$this->label_class}\"" : "";
        $output .= "<label for=\"{$id}\"{$label_class}>{$requiredbefore}".$this->get_text($this->title)."{$requiredafter}</label>\n";
      } else {
        if( !in_array('title', array_keys($this->attributes)) ){
          $this->attributes['title'] = strip_tags($this->get_text($this->title).$required);
        }

        $id = $this->get_html_id();
        $form->add_js("\$('#{$id}','#{$form->get_id()}').tooltip();");
      }
    }

    if($this->with_geocode == TRUE){
      $output .= $this->geocode_box->render($form); // ."<button id=\"{$id}_searchbox_btn\">".$this->get_text('search')."</button>";
    }

    if($this->with_map == TRUE){
      $mapattributes = ' class="gmap"';
      $output .= "<div id=\"{$id}-map\"{$mapattributes}></div>\n";
    }

    $output .= $this->latitude->render($form);
    $output .= $this->longitude->render($form);

    if($this->with_current_location == TRUE){
      $output .= $this->current_location_btn->render($form);
    }

    if($this->with_reverse == TRUE){
      $output .= $this->reverse_geocode_box->render($form);
    }

    $output .= "</{$this->tag}>\n";
    return $output;
  }
}

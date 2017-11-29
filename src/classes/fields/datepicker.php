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
use Degami\PHPFormsApi\Base\field;

/**
 * the datepicker text input field class
 */
class datepicker extends field {

  /**
   * date format
   * @var string
   */
  protected $date_format = 'yy-mm-dd';

  /**
   * change month flag
   * @var boolean
   */
  protected $change_month = FALSE;

  /**
   * change year flag
   * @var boolean
   */
  protected $change_year = FALSE;

  /**
   * min date
   * @var string
   */
  protected $mindate = '-10Y';

  /**
   * max date
   * @var string
   */
  protected $maxdate = '+10Y';

  /**
   * year range
   * @var string
   */
  protected $yearrange = '-10:+10';

  /**
   * disabled dates array
   * @var array
   */
  protected $disabled_dates = []; // an array of date strings compliant to $date_format

  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();

    $dateFormat = $this->date_format;
    $changeMonth = ($this->change_month) ? 'true'  :'false';
    $changeYear = ($this->change_year == TRUE) ? 'true'  :'false';

    $this->add_js(
      preg_replace("/\s+/"," ",str_replace("\n","","".
        ((count($this->disabled_dates)>0) ? "var disabled_dates_array_{$form->get_id()}_{$id} = ".json_encode((array) $this->disabled_dates).";" : "")."
            \$('#{$id}','#{$form->get_id()}').datepicker({
            dateFormat: '{$this->date_format}',
            ".( (count($this->disabled_dates)>0) ? "beforeShowDay: function(date){
              var string = $.datepicker.formatDate('{$this->date_format}', date);
              return [ disabled_dates_array_{$form->get_id()}_{$id}.indexOf(string) == -1 ];
            },": "")."
            changeMonth: {$changeMonth},
            changeYear: {$changeYear},
            minDate: \"{$this->mindate}\",
            maxDate: \"{$this->maxdate}\",
            yearRange: \"{$this->yearrange}\"
          });")));

    parent::pre_render($form);
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();

    if(!isset($this->attributes['class'])) $this->attributes['class'] = '';
    if ($this->has_errors()) {
      $this->attributes['class'] .= ' has-errors';
    }
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes();

    $output = "<input type=\"text\" id=\"{$id}\" name=\"{$this->name}\" size=\"{$this->size}\" value=\"{$this->value}\"{$attributes} />\n";

    return $output;
  }

  /**
   * is_a_value hook
   * @return boolean this is a value
   */
  public function is_a_value(){
    return TRUE;
  }
}

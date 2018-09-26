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
 * the datepicker text input field class
 */
class Datepicker extends Textfield
{
    /**
     * date format
     *
     * @var string
     */
    protected $date_format = 'yy-mm-dd';

    /**
     * change month flag
     *
     * @var boolean
     */
    protected $change_month = false;

    /**
     * change year flag
     *
     * @var boolean
     */
    protected $change_year = false;

    /**
     * min date
     *
     * @var string
     */
    protected $mindate = '-10Y';

    /**
     * max date
     *
     * @var string
     */
    protected $maxdate = '+10Y';

    /**
     * year range
     *
     * @var string
     */
    protected $yearrange = '-10:+10';

    /**
     * disabled dates array
     *
     * @var array
     */
    protected $disabled_dates = []; // an array of date strings compliant to $date_format

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

        $changeMonth = ($this->change_month) ? 'true'  :'false';
        $changeYear = ($this->change_year == true) ? 'true'  :'false';

        $this->addJs(
            ((count($this->disabled_dates)>0) ?
                "var disabled_dates_array_{$form->getId()}_{$id} = ".
                json_encode((array) $this->disabled_dates).";":
                ""
            ).
            "\$('#{$id}','#{$form->getId()}').datepicker({
              dateFormat: '{$this->date_format}',
              ".((count($this->disabled_dates)>0) ? "beforeShowDay: function(date){
                var string = $.datepicker.formatDate('{$this->date_format}', date);
                return [ disabled_dates_array_{$form->getId()}_{$id}.indexOf(string) == -1 ];
                },": "")."
              changeMonth: {$changeMonth},
              changeYear: {$changeYear},
              minDate: \"{$this->mindate}\",
              maxDate: \"{$this->maxdate}\",
              yearRange: \"{$this->yearrange}\"
            });"
        );

        parent::preRender($form);
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean this is a value
     */
    public function isAValue()
    {
        return true;
    }
}

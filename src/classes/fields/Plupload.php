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
use Degami\PHPFormsApi\Abstracts\Base\Field;

/**
 * the pupload field class
 */
class Plupload extends Field
{

    /**
     * filters
     *
     * @var array
     */
    protected $filters = [];

    /**
     * upload.php url
     *
     * @var string
     */
    protected $url     = ''; // url upload.php

    /**
     * Moxie.swf url
     *
     * @var string
     */
    protected $swf_url = ''; // url Moxie.swf

    /**
     * Moxie.xap url
     *
     * @var string
     */
    protected $xap_url = ''; // url Moxie.xap

    /**
     * process hook
     *
     * @param mixed $value value to set
     */
    public function process($value)
    {
        $this->value = json_decode($value);
    }

    /**
     * pre_render hook
     *
     * @param Form $form form object
     */
    public function preRender(Form $form)
    {
        if ($this->pre_rendered == true) {
            return;
        }
        $id = $this->getHtmlId();
        $form_id = $form->getId();

        $this->addJs(
            "
      var {$id}_files_remaining = 0;
      $('#{$id}_uploader').pluploadQueue({
        runtimes : 'html5,flash,silverlight,html4',
        chunk_size : '1mb',
        unique_names : true,

        resize : {width : 320, height : 240, quality : 90},

        url : '{$this->url}',
        flash_swf_url : '{$this->swf_url}',
        silverlight_xap_url : '{$this->xap_url}',
        filters : ".json_encode($this->filters).",

        preinit : {
            Init: function(up, info) {
            },

            UploadFile: function(up, file) {
            }
        },

        init : {
            FileUploaded: function(up, file, info) {
                response = JSON.parse( info.response );

                if(file.status == plupload.DONE && response.result == null){
                  var value = \$.trim( \$('#{$id}_uploaded_json').val() );
                  if(value != '') {value = JSON.parse( value );}
                  else value = [];
                  if(value == null) value = [];
                  var obj = {temppath: response.temppath, name: file.name};
                  value.push( obj );

                  \$('#{$id}_uploaded_json').val( JSON.stringify(value) );
                }
            },

            FilesRemoved: function(up, files) {
              plupload.each(files, function(file) {
                {$id}_files_remaining--;
              });
              if({$id}_files_remaining == 0){
                \$('#{$form_id} input[type=submit]').removeAttr('disabled');
              }
            },

            FilesAdded: function(up, files) {
              \$('#{$form_id} input[type=submit]').attr('disabled','disabled');
              plupload.each(files, function(file) {
                {$id}_files_remaining++;
              });
            },

            UploadComplete: function(up, file, info) {
              \$('#{$form_id} input[type=submit]').removeAttr('disabled');
              {$id}_files_remaining = 0;
            },

            Error: function(up, args) {
                log('[Error] ', args);
            }
        }
    });


    function log() {
        var str = '';

        plupload.each(arguments, function(arg) {
            var row = '';

            if (typeof(arg) != 'string') {
                plupload.each(arg, function(value, key) {
                    if (arg instanceof plupload.File) {
                        switch (value) {
                            case plupload.QUEUED:
                                value = 'QUEUED';
                                break;

                            case plupload.UPLOADING:
                                value = 'UPLOADING';
                                break;

                            case plupload.FAILED:
                                value = 'FAILED';
                                break;

                            case plupload.DONE:
                                value = 'DONE';
                                break;
                        }
                    }

                    if (typeof(value) != 'function') {
                        row += (row ? ', ' : '') + key + '=' + value;
                    }
                });

                str += row + ' ';
            } else {
                str += arg + ' ';
            }
        });

        var \$log = \$('#{$id}_log');
        \$('<div>'+str+'</div>').appendTo(\$log)
    }"
        );

        parent::preRender($form);
    }

    /**
     * render_field hook
     *
     * @param Form $form form object
     *
     * @return string        the element html
     */
    public function renderField(Form $form)
    {
        $id = $this->getHtmlId();

        return "<div id=\"{$id}_uploader\"><p>Your browser doesn't have Flash, Silverlight or HTML5 support.</p></div>
                <div id=\"{$id}_log\"></div>
                <input type=\"hidden\" id=\"{$id}_uploaded_json\" name=\"{$this->name}\" value=\"".
                json_encode($this->value).
                "\" />";
    }

    /**
     * is_a_value hook
     *
     * @return boolean this is a value
     */
    public function isAValue()
    {
        return true;
    }
}

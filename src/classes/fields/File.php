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
use Degami\PHPFormsApi\Abstracts\Base\Field;
use Degami\PHPFormsApi\Accessories\TagElement;
use Degami\PHPFormsApi\Accessories\TagList;

/**
 * The file input field class
 */
class File extends Field
{
    /**
     * "file already uploaded" flag
     *
     * @var boolean
     */
    protected $uploaded = false;

    /**
     * file destination directory
     *
     * @var string
     */
    protected $destination;

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

        $form->setAttribute('enctype', 'multipart/form-data');

        if (!isset($this->attributes['class'])) {
            $this->attributes['class'] = '';
        }
        if ($this->hasErrors()) {
            $this->attributes['class'] .= ' has-errors';
        }
        if ($this->disabled == true) {
            $this->attributes['disabled']='disabled';
        }

        $tag = new TagList();
        $tag->addChild(new TagElement([
            'tag' => 'input',
            'type' => 'hidden',
            'name' => $this->name,
            'value' => $this->name,
        ]));

        $tag->addChild(new TagElement([
            'tag' => 'input',
            'type' => 'file',
            'id' => $id,
            'name' => $this->name,
            'value_needed' => false,
            'attributes' => $this->attributes + ['size' => $this->size],
        ]));

        return $tag;
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $value value to set
     */
    public function processValue($value)
    {
        $this->value = [
            'filepath' => (isset($value['filepath'])) ?
              $value['filepath'] :
              $this->destination .'/'. basename($_FILES[$this->getName()]['name']),
            'filename' => (isset($value['filename'])) ?
              $value['filename'] :
              basename($_FILES[$this->getName()]['name']),
            'filesize' => (isset($value['filesize'])) ?
              $value['filesize'] :
              $_FILES[$this->getName()]['size'],
            'mimetype' => (isset($value['mimetype'])) ?
              $value['mimetype'] :
              $_FILES[$this->getName()]['type'],
        ];
        if (isset($value['uploaded'])) {
            $this->uploaded = $value['uploaded'];
        }
        if ($this->isValid()) {
            if (@move_uploaded_file($_FILES[$this->getName()]['tmp_name'], $this->value['filepath']) == true) {
                $this->uploaded = true;
            }
        }
    }

    /**
     * Check if file was uploaded
     *
     * @return boolean TRUE if file was uploaded
     */
    public function isUploaded()
    {
        return $this->uploaded;
    }

    /**
     * "required" validation function
     *
     * @param  mixed $value the element value
     * @return mixed        TRUE if valid or a string containing the error message
     */
    public static function validateRequired($value = null)
    {
        if (!empty($value)
            && (isset($value['filepath']) && !empty($value['filepath']))
            && (isset($value['filename']) && !empty($value['filename']))
            && (isset($value['mimetype']) && !empty($value['mimetype']))
            && (isset($value['filesize']) && $value['filesize']>=0)
        ) {
            return true;
        } else {
            return "<em>%t</em> is required";
        }
    }

    /**
     * validate function
     *
     * @return boolean this field is always valid
     */
    public function isValid()
    {
        if ($this->uploaded) {
            return true;
        }
        return parent::isValid();
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

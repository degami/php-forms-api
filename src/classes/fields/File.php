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

use Degami\Basics\Html\BaseElement;
use Degami\PHPFormsApi\Form;
use Degami\PHPFormsApi\Abstracts\Base\Field;
use Degami\Basics\Html\TagElement;
use Degami\Basics\Html\TagList;

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
     * rename destination file if it already exists
     * 
     * @var boolean
     */
    protected $rename_on_existing = false;

    /**
     * {@inheritdoc}
     *
     * @param Form $form form object
     *
     * @return string|BaseElement        the element html
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
        if (($requestValue = static::traverseArray($this->convertFilesArray(), $this->getName())) != null) {
            $this->value = [
                'filepath' => (isset($value['filepath'])) ?
                    $value['filepath'] :
                    $this->destination .'/'. basename($requestValue['name']),
                'filename' => (isset($value['filename'])) ?
                    $value['filename'] :
                    basename($requestValue['name']),
                'filesize' => (isset($value['filesize'])) ?
                    $value['filesize'] :
                    $requestValue['size'],
                'mimetype' => (isset($value['mimetype'])) ?
                    $value['mimetype'] :
                    $requestValue['type'],
            ];
        }

        if (isset($value['uploaded'])) {
            $this->uploaded = $value['uploaded'];
        }
        if (($requestValue['size'] ?? 0) == 0) {
            $this->uploaded = false;
        } else {
            $this->uploaded = true;
        }

        if (!$this->uploaded) {
            return;
        }

        if ($this->isValid()) {
            if ($this->rename_on_existing) {
                $filepath = $this->value['filepath']; $counter = 0;
                do {
                    if (!file_exists($filepath)) {
                        break;
                    }

                    $path_parts = pathinfo($filepath);
                    $filepath = $path_parts['dirname'] . '/' . $path_parts['filename'] . '_' . (++$counter) . '.' . $path_parts['extension'];
                } while (file_exists($filepath));

                if ($filepath != $this->value['filepath']) {
                    $this->value['renamed'] = true;
                }

                $this->value['filepath'] = $filepath;
                $this->value['filename'] = basename($filepath);
            }

            if (@move_uploaded_file($_FILES[$this->getName()]['tmp_name'], $this->value['filepath']) == true) {
                $this->uploaded = true;
            }
        }
    }

    protected function convertFilesArray(): array
    {
        $out = [];
        foreach ($_FILES as $input_name => $input_value) {
            foreach (['name','type','tmp_name','error','size'] as $prop) {
                if (is_array($input_value[$prop])) {
                    foreach ($input_value[$prop] as $key => $value) {
                        $out[$input_name][$key][$prop] = $value ?? null;
                    }
                } else {
                     $out[$input_name][$prop] = $input_value[$prop] ?? null;
                }
            }
        }
        return $out;
    }

    /**
     * Check if file was uploaded
     *
     * @return boolean TRUE if file was uploaded
     */
    public function isUploaded(): bool
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
    public function isValid() : bool
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
    public function isAValue() : bool
    {
        return true;
    }
}

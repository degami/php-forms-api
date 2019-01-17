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

namespace Degami\PHPFormsApi\Abstracts\Fields;

use Degami\PHPFormsApi\Abstracts\Base\Element;

/**
 * The optionable field class
 */
abstract class Optionable extends Element
{
    /**
     * option label
     *
     * @var string
     */
    protected $label;

    /**
     * Get the element label
     *
     * @return mixed the element label
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set the element label
     *
     * @param  mixed $label element label
     * @return Option
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Class constructor
     *
     * @param string $label   label
     * @param array  $options options array
     */
    public function __construct($label, $options)
    {
        parent::__construct();
        $this->setLabel($label);

        $this->setClassProperties($options);
    }
}

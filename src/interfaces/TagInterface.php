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
   ####                  FIELD INTERFACE                ####
   ######################################################### */

namespace Degami\PHPFormsApi\Interfaces;

/**
 * tag interface
 */
interface TagInterface
{

    /**
     * Add child to tag
     *
     * @param mixed $child tag to add, can be a tag object or a string
     */
    public function addChild($child);

    /**
     * render tag html
     *
     * @return string tag html
     */
    public function renderTag();
}

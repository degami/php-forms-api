<?php
/**
 * PHP FORMS API
 *
 * @category Utils
 * @package  Degami\PHPFormsApi
 * @author   Mirko De Grandis <degami@github.com>
 * @license  MIT https://opensource.org/licenses/mit-license.php
 * @link     https://github.com/degami/php-forms-api
 */
/* #########################################################
   ####                 ACCESSORIES                     ####
   ######################################################### */

namespace Degami\PHPFormsApi\Accessories;

use Degami\Basics\MultiLevelDataBag;

/**
 * A class to hold form fields submitted values
 */
class FormValues extends MultiLevelDataBag
{
    /**
     * onChange hook
     */
    public function onChange()
    {
    }
}

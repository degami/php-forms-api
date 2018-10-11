<?php
/**
 * PHP FORMS API
 *
 * @package degami/php-forms-api
 */
/* #########################################################
   ####                 ACCESSORIES                     ####
   ######################################################### */

namespace Degami\PHPFormsApi\Accessories;

use Degami\PHPFormsApi\Abstracts\Base\MultiLevelDataBag;

/**
 * a class to hold form fields submitted values
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

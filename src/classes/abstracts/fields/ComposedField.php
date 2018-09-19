<?php
/**
 * PHP FORMS API
 *
 * @package degami/php-forms-api
 */
/* #########################################################
   ####                    FIELDS                       ####
   ######################################################### */

namespace Degami\PHPFormsApi\Abstracts\Fields;

use Degami\PHPFormsApi\Containers\TagContainer;

/**
 * the composed field class
 */
abstract class ComposedField extends TagContainer
{
    /**
     * is_a_value hook
     *
     * @return boolean this is a value
     */
    public function isAValue()
    {
        return true;
    }

    /**
     * on_add_return overload
     *
     * @return string 'parent'
     */
    public function onAddReturn()
    {
        return 'parent';
    }
}

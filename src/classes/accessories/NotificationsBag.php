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
   ####                 ACCESSORIES                     ####
   ######################################################### */

namespace Degami\PHPFormsApi\Accessories;

use Degami\PHPFormsApi\Abstracts\Base\MultiLevelDataBag;

/**
 * A class to hold notifications
 */

class NotificationsBag extends MultiLevelDataBag
{
    /**
     * render notifications
     *
     * @param string the notification group
     *
     * @return string the notification list html
     */
    public function renderHTML($group = null)
    {
        if ($group == null) {
            $out = "";
            foreach ($this->keys() as $group) {
                if (($this->{$group} instanceof NotificationsBag && $this->{$group}->count() > 0) ||
                    (is_array($this->{$group}) && count($this->{$group}) > 0)
                ) {
                    $out .= "<li class=\"{$group}-item\">".
                            implode("</li><li class=\"{$group}-item\">", $this->{$group}->toArray()).
                            "</li>";
                } else {
                    $out .= "<li class=\"{$group}-item\">".$this->{$group}."</li>";
                }
            }
            return $out;
        }
        if (!isset($this->{$group}) || $this->{$group}->count() == 0) {
            return '';
        }
        return "<li class=\"{$group}-item\">".
                    implode(
                        "</li><li class=\"{$group}-item\">",
                        $this->{$group}->toArray()
                    ).
                    "</li>";
    }

    /**
     * onChange hook
     */
    public function onChange()
    {
    }
}

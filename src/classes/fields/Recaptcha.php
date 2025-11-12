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
   ####                    FIELDS                       ####
   ######################################################### */

namespace Degami\PHPFormsApi\Fields;

use Degami\Basics\Html\BaseElement;
use Degami\PHPFormsApi\Form;
use Degami\PHPFormsApi\Abstracts\Fields\Captcha;

/**
 * The recaptcha field class
 */
class Recaptcha extends Captcha
{

    /**
     * public key
     *
     * @var string
     */
    protected $publickey = '';

    /**
     * private key
     *
     * @var string
     */
    protected $privatekey = '';


    /**
     * {@inheritdoc}
     *
     * @param Form $form form object
     *
     * @return string|BaseElement        the element html
     */
    public function renderField(Form $form)
    {
        if (!function_exists('recaptcha_get_html')) {
            return '';
        }
        return recaptcha_get_html($this->publickey);
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean TRUE if element is valid
     */
    public function isValid() : bool
    {
        if ($this->already_validated == true) {
            return true;
        }
        if (isset($this->value['already_validated']) && $this->value['already_validated'] == true) {
            return true;
        }
        if (!function_exists('recaptcha_check_answer')) {
            $this->already_validated = true;
            return true;
        }

        if (!is_array($this->value)) {
            $this->value = [];
        }

        // if something is missing...
        $this->value += [
            'challenge_field' => '',
            'response_field' => '',
        ];

        $resp = recaptcha_check_answer(
            $this->privatekey,
            $_SERVER["REMOTE_ADDR"],
            $this->value["challenge_field"],
            $this->value["response_field"]
        );
        if (!$resp->is_valid) {
            $this->addError($this->getText("Recaptcha response is not valid"), __FUNCTION__);
        } else {
            $this->already_validated = true;
            $this->value['already_validated'] = true;
        }

        return $resp->is_valid;
    }

    /**
     * {@inheritdoc}
     *
     * @param array $request request array
     */
    public function alterRequest(array &$request)
    {
        foreach ($request as $key => $val) {
            //RECAPTCHA HANDLE
            if (preg_match('/^recaptcha\_(challenge|response)\_field$/', $key, $matches)) {
                $fieldname = $this->getName();
                if (!empty($request["recaptcha_challenge_field"])) {
                    $request[$fieldname]["challenge_field"] = $request["recaptcha_challenge_field"];
                    unset($request["recaptcha_challenge_field"]);
                }
                if (!empty($request["recaptcha_response_field"])) {
                    $request[$fieldname]["response_field"] = $request["recaptcha_response_field"];
                    unset($request["recaptcha_response_field"]);
                }
            }
        }
    }
}

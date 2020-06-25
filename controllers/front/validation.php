<?php
/**
* 2018 Jorge Vargas.
*
* NOTICE OF LICENSE
*
* This source file is subject to the End User License Agreement (EULA)
*
* See attachmente file LICENSE
*
* @author    Jorge Vargas <https://addons.prestashop.com/contact-form.php?id_product=31085>
* @copyright 2007-2018 Jorge Vargas
*
* @see      http://addons.prestashop.com/es/2_community?contributor=3167
*
* @license   End User License Agreement (EULA)
*
* @version   2.0
*/
include_once _PS_MODULE_DIR_.'pagaenlinea/libraries/browser/Browser.php';

class PagaenlineaValidationModuleFrontController extends ModuleFrontController
{
    public $content_only = false;
    public $ssl = true;
    public $display_column_left = false;
    public $display_column_right = false;
    public $auth = true;

    /**
     * This class should be use by your Instant Payment
     * Notification system to validate the order remotely.
     */
    public function postProcess()
    {
        /*
         * If the module is not active anymore, no need to process anything.
         */
        if (false == $this->module->active) {
            die;
        }

        $requiredVars = array(
            'id_cart',
            'id_module',
            'id_order',
            'secure_key',
            'idTransaccion',
        );

        foreach ($requiredVars as $k) {
            if (!Tools::getIsset($k)) {
                $this->errors[] = $this->module->l('Missing parameter: ').$k;
            }
        }

        $id_cart = $id_module = $id_order = $secure_key = $idTransaccion = null;
        foreach ($_GET as $key => $value) {
            if (in_array($key, $requiredVars)) {
                $$key = pSQL($value);
            }
        }

        if (!$this->errors) {
            $client = new Classes_RbmApi();
            $client->setIdTransaccion($idTransaccion);
            $client->setIdTransaccionTerminal($id_cart);

            $errors = $client->getErrors();
            if (count($errors)) {
                array_push($this->errors, $errors);
            } else {
                $response = $client->consultarEstadoDePago($idTransaccion);
                $this->context->smarty->assign(array(
                    'buttonUrl' => $client->getButtonUrl($idTransaccion),
                    'response' => $response,
                ));
            }

            // Save in DB
            $this->module->addTransaction((int) $id_order, (int) $idTransaccion, (bool) $client->is_test);
        } else {
            $this->errors[] = $this->module->l('An error occurred when trying to generate payment.');
        }

        // Check browser
        $browser = new Browser();

        $this->context->smarty->assign(array(
            'redirectUrl' => Tools::url(
                $this->context->link->getPageLink('order-confirmation', true),
                http_build_query(array(
                    'idTransaccion' => isset($idTransaccion) && $idTransaccion ? $idTransaccion : null,
                    'id_cart' => $id_cart,
                    'id_module' => $id_module,
                    'id_order' => $id_order,
                    'key' => $secure_key,
                    'browser' => $browser,
                ))
            ),
        ));

        $this->setTemplate('module:pagaenlinea/views/templates/front/validation.tpl');
    }

    public function setMedia()
    {
        parent::setMedia();
        $this->addJS($this->module->getPathUri().'views/js/front.js');
        $this->addJS($this->module->getPathUri().'views/js/jquery.fancybox.min.js');

        $this->addCSS($this->module->getPathUri().'views/css/front.css', 'all');
        $this->addCSS($this->module->getPathUri().'views/css/jquery.fancybox.min.css', 'all');
    }
}

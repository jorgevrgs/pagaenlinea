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
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once 'classes/RbmApi.php';
include_once 'models/Orders.php';

class PagaEnLinea extends PaymentModule
{
    public $tpls = array(
        'hook-return' => 'module:pagaenlinea/views/templates/hook/return.tpl',
        'hook-payment-top' => 'module:pagaenlinea/views/templates/hook/payment-top.tpl',
        'front-help' => 'module:pagaenlinea/views/templates/front/help.tpl',
        'admin-configure' => 'module:pagaenlinea/views/templates/admin/configure.tpl',
        'admin-order-content' => 'module:pagaenlinea/views/templates/admin/admin-order-content.tpl',
        'admin-order-tab' => 'module:pagaenlinea/views/templates/admin/admin-order-tab.tpl',
    );

    public $errors = array();
    public $warnings = array();

    public function __construct()
    {
        $this->name = 'pagaenlinea';
        $this->tab = 'payments_gateways';
        $this->version = '2.0.1';
        $this->author = 'Jorge Vargas';
        $this->need_instance = 1;
        $this->controllers = array('confirmation', 'validation');
        $this->module_key = '40ff3d8c92d2dfbd75a2404c669b1f70';
        $this->author_address = '0x3838debece3737162016e385f881b248154880d3';

        /*
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Paga En Linea by RBM');
        $this->description = $this->l(
            'Paga En Linea by Redeban Multicolor RBM, receive credit card, HTTPS and INCOCREDITO required'
        );

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall my module?');

        $this->limited_countries = array('CO');
        $this->limited_currencies = array('COP');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->decodeLang = array(
            'franquicia' => $this->l('Franchise'),
            'tipoMedioDePago' => $this->l('Means of payment'),
            'fechaTransaccion' => $this->l('Transaction date'),
            'numeroAprobacion' => $this->l('Approval number'),
            'montoTotal' => $this->l('Total amount'),
            'costoTransaccion' => $this->l('Transaction cost'),
            'idTransaccionAutorizador' => $this->l('Id transaction authorizer'),
            'codRespuesta' => $this->l('Response code'),
            'descRespuesta' => $this->l('Response description'),
            'estado' => $this->l('Status'),
        );
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update.
     */
    public function install()
    {
        if (false === extension_loaded('soap')) {
            $this->errors[] = $this->l('You have to enable the SOAP extension on your server to install this module');

            return false;
        }

        $iso_code = Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT'));

        if (false == in_array($iso_code, $this->limited_countries)) {
            $this->errors[] = $this->l('This module is not available in your country');

            return false;
        }

        Configuration::updateValue('PAGAENLINEA_LIVE_MODE', false);
        Configuration::updateValue('PAGAENLINEA_TEST_IP', pSQL(Tools::getRemoteAddr()));

        return parent::install()
        && $this->registerHook('backOfficeHeader')
        && $this->registerHook('actionPaymentCCAdd')
        && $this->registerHook('actionPaymentConfirmation')
        && $this->registerHook('displayOrderConfirmation')
        && $this->registerHook('displayHeader')
        && $this->registerHook('displayPaymentReturn')
        && $this->registerHook('displayPaymentTop')
        && $this->registerHook('displayAdminOrderContentOrder')
        && $this->registerHook('displayAdminOrderTabOrder')
        && $this->registerHook('paymentOptions')
        && $this->installDb();
    }

    protected function installDb()
    {
        $sql = array();
        $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'order_pagaenlinea`(
        `id_order_pagaenlinea` int(11) unsigned NOT NULL auto_increment,
        `id_order` int(11) unsigned NOT NULL,
        `id_transaction` varchar(128) NOT NULL,
        `id_terminal` varchar(32) NOT NULL,
        `id_acquirer` varchar(128) NOT NULL,
        `is_test` BOOLEAN NOT NULL,
        PRIMARY KEY (`id_order_pagaenlinea`),
        KEY `id_order` (`id_order`),
        KEY `id_transaction` (`id_transaction`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
        foreach ($sql as $query) {
            if (false == Db::getInstance()->execute($query)) {
                return false;
            }
        }

        return true;
    }

    public function uninstall()
    {
        //Configuration::deleteByName('PAGAENLINEA_LIVE_MODE');
        //Configuration::deleteByName('PAGAENLINEA_TEST_IP');

        //if ($this->uninstallDb()
        //&& parent::uninstall()) {
        //    return true;
        //}

        return true;
    }

    protected function uninstallDb()
    {
        $sql = array();
        $sql[] = 'DROP TABLE IF EXISTS '._DB_PREFIX_.'order_pagaenlinea;';

        foreach ($sql as $query) {
            if (false == Db::getInstance()->execute($query)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Load the configuration form.
     */
    public function getContent()
    {
        /*
         * If values have been submitted in the form, process.
         */
        if (true == ((bool) Tools::isSubmit('submitPagaenlineaModule'))) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->display(__FILE__, 'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitPagaenlineaModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'PAGAENLINEA_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-user"></i>',
                        'label' => $this->l('ID user'),
                        'name' => 'PAGAENLINEA_ID_USUARIO',
                        'desc' => $this->l('ID user is giving by RBM'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-lock"></i>',
                        'label' => $this->l('Password'),
                        'name' => 'PAGAENLINEA_CLAVE',
                        'desc' => $this->l('Password is giving by RBM'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-phone"></i>',
                        'label' => $this->l('ID Terminal 1'),
                        'name' => 'PAGAENLINEA_ID_TERMINAL_1',
                        'desc' => $this->l('ID Terminal 1 is giving by RBM'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-phone"></i>',
                        'label' => $this->l('ID Terminal 2'),
                        'name' => 'PAGAENLINEA_ID_TERMINAL_2',
                        'desc' => $this->l('ID Terminal 2 is giving by RBM'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-briefcase"></i>',
                        'label' => $this->l('ID Adquirente'),
                        'name' => 'PAGAENLINEA_ID_ADQUIRENTE',
                        'desc' => $this->l('ID Adquirente is giving by RBM'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-pushpin"></i>',
                        'label' => $this->l('Test IP'),
                        'name' => 'PAGAENLINEA_TEST_IP',
                        'desc' => $this->l(
                            'Comma separated value of your IP for test purpose, empty mean disabled for all.'
                        ).' '.sprintf($this->l('Your IP address is %s'), pSQL(Tools::getRemoteAddr())),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'PAGAENLINEA_LIVE_MODE' => Configuration::get('PAGAENLINEA_LIVE_MODE', false),
            'PAGAENLINEA_ID_USUARIO' => Configuration::get('PAGAENLINEA_ID_USUARIO', 'testCompany'),
            'PAGAENLINEA_CLAVE' => Configuration::get('PAGAENLINEA_CLAVE', 'testCompany.2017'),
            'PAGAENLINEA_ID_ADQUIRENTE' => Configuration::get('PAGAENLINEA_ID_ADQUIRENTE', '0011223344'),
            'PAGAENLINEA_ID_TERMINAL_1' => Configuration::get('PAGAENLINEA_ID_TERMINAL_1', 'ESB10001'),
            'PAGAENLINEA_ID_TERMINAL_2' => Configuration::get('PAGAENLINEA_ID_TERMINAL_2', 'ESB10002'),
            'PAGAENLINEA_TEST_IP' => Configuration::get('PAGAENLINEA_TEST_IP', '127.0.0.1'),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookDisplayHeader()
    {
        // TODO
    }

    /**
     * @param array $params {
     *
     *   @var OrderPayment $paymentCC {
     *     @var string order_reference
     *     @var string id_currency
     *     @var string amount
     *     @var string payment_method
     *     @var string conversion_rate
     *     @var string transaction_id
     *     @var string card_number
     *     @var string card_brand
     *     @var string card_expiration
     *     @var string card_holder
     *     @var string date_add
     *   }
     * }
     */
    public function hookActionPaymentCCAdd($params)
    {
        $orderPayment = $params['paymentCC'];

        if (Tools::getIsset('idTransaccion')
        && Tools::getIsset('id_cart')
        && Validate::isLoadedObject($orderPayment)
        && $orderPayment->id) {
            $id_cart = (int) Tools::getValue('id_cart');
            $idTransaccion = (int) Tools::getValue('idTransaccion');

            $client = new Classes_RbmApi();
            $client->setIdTransaccion($idTransaccion);
            $client->setIdTransaccionTerminal($id_cart);

            if ($client->consultarEstadoDePago()) {
                $response = $client->getLastResponse();
                if (isset($response['infoPago'])) {
                    $orderPayment->transaction_id = $response['infoPago']['numeroAprobacion'];
                    $orderPayment->payment_method = $response['infoPago']['tipoMedioDePago'];
                    $orderPayment->card_brand = $response['infoPago']['franquicia'];
                    $orderPayment->date_add = date(
                        'Y-m-d H:i:s',
                        strtotime($response['infoPago']['fechaTransaccion'])
                    );

                    $orderPayment->update();
                }
            }

            // Save in database
            $id_order = Order::getIdByCartId($id_cart);

            return $this->addTransaction($id_order, $idTransaccion, $client->is_test);
        }
    }

    /**
     * @param int $id_order
     * @param int $id_transaction
     * @param string $id_terminal
     *
     * @uses Validate::isLoadedObject()
     * @uses Models_Orders::getTransactionByIdOrder()
     * @uses Models_Orders::save()
     */
    public function addTransaction($id_order, $id_transaction, $is_test = false)
    {
        if (!$id_order
        || !$id_transaction) {
            return false;
        }
        $transactionInfo = Models_Orders::getTransactionByIdOrder($id_order);

        $id_order_pagaenlinea = isset($transactionInfo['id_order_pagaenlinea'])
        && $transactionInfo['id_order_pagaenlinea']
            ? $transactionInfo['id_order_pagaenlinea']
            : null;

        $transaction = new Models_Orders($id_order_pagaenlinea);
        if (Validate::isLoadedObject($transaction)) {
            return true;
        }

        $transaction->id_order = (int) $id_order;
        $transaction->id_transaction = (int) $id_transaction;
        $transaction->is_test = (bool) $is_test;
        $transaction->id_terminal = (string) Configuration::get('PAGAENLINEA_ID_TERMINAL_1');
        $transaction->id_acquirer = (string) Configuration::get('PAGAENLINEA_ID_ADQUIRENTE');

        return $transaction->save();
    }

    /**
     * @param array $params {
     *
     *   @var int $id_order
     * }
     *
     * @see OrderHistory::changeIdOrderState
     */
    public function hookActionPaymentConfirmation($params)
    {
        //$id_order = (int)$params['id_oder'];
        // TODO
    }

    /**
     * No dependency, only Order object.
     *
     * @param array $params {
     *
     *   @var Order $order
     * }
     *
     * @see OrderConfirmationController::initContent->displayOrderConfirmation($order)
     */
    public function hookDisplayOrderConfirmation($params)
    {
        $order = $params['order'];

        if (false == $this->active
        || empty($order)
        || !Validate::isLoadedObject($order)) {
            return false;
        }

        $id_cart = (int) $order->id_cart;

        if (!Tools::getIsset('idTransaccion')) {
            $this->errors[] = $this->l('idTransaccion value is missing');
        } else {
            $idTransaccion = (int) Tools::getValue('idTransaccion');

            $client = new Classes_RbmApi();
            $client->setIdTransaccion($idTransaccion);
            $client->setIdTransaccionTerminal($id_cart);

            if ($client->consultarEstadoDePago()) {
                $lastResponse = $client->getLastResponse();
                $this->updateOrderTransaction($lastResponse, $order);

                $this->context->smarty->assign(array(
                    'response' => $lastResponse,
                    'idTransaccion' => $idTransaccion,
                ));
            }

            if ($client->is_test) {
                $this->warnings [] = $this->l(
                    '[REDEBAN WARNING]: Remember that this transaction was made in test mode'
                );
            }

            $this->errors = array_merge($this->errors, $client->getErrors());
        }

        $idCurrenteState = (int) $order->getCurrentOrderState()->id;
        switch ($idCurrenteState) {
            case Configuration::get('PS_OS_PAYMENT'):
            case Configuration::get('PS_OS_OUTOFSTOCK_PAID'):
                $this->smarty->assign('status', 'payment');
                break;
            case Configuration::get('PS_OS_CANCELED'):
                $this->smarty->assign('status', 'canceled');
                break;
            case Configuration::get('PS_OS_ERROR'):
            default:
                $this->smarty->assign('status', 'error');
        }

        $this->smarty->assign(array(
            'id_order'    => $order->id,
            'reference'   => $order->reference,
            'shop_name'   => $this->context->shop->name,
            'decode_lang' => $this->decodeLang,
            'errors'      => $this->errors,
            'warnings'    => $this->warnings,
        ));

        return $this->fetch($this->tpls['hook-return']);
    }

    public static function getOrderStatus($status)
    {
        switch ($status) {
            case 'Aprobada':
                $idOrderStatus = Configuration::get('PS_OS_PAYMENT');
                break;
            case 'Iniciada':
                $idOrderStatus = Configuration::get('PS_OS_CANCELED');
                break;
            case 'Error':
            case 'Rechazada':
            case 'ErrorConsulta':
            default:
                $idOrderStatus = Configuration::get('PS_OS_ERROR');
        }

        return $idOrderStatus;
    }

    /**
     * Depends on module Object.
     *
     * @param array $params {
     *
     *   @var Order $order
     * }
     *
     * @see OrderConfirmationController::initContent->displayPaymentReturn($order)
     */
    public function hookDisplayPaymentReturn($params)
    {
        // TODO delete
    }

    public function hookDisplayPaymentTop()
    {
        return $this->fetch($this->tpls['hook-payment-top']);
    }

    /**
     * @param array $params {
     *
     *   @var object $order
     *   @var object $customer
     *   @var array $products
     * }
     */
    public function hookDisplayAdminOrderContentOrder($params)
    {
        $order = $params['order'];
        $id_order = (int) $order->id;
        $transaction = Models_Orders::getTransactionByIdOrder($id_order);
        $idTransaccion = (int) $transaction['id_transaction'];

        if ($idTransaccion) {
            $id_cart = $order->id_cart;

            $client = new Classes_RbmApi();
            $client->setIdTransaccion($idTransaccion);
            $client->setIdTransaccionTerminal($id_cart);
            $client->setIdTerminal($transaction['id_terminal']);
            $client->setIdAdquiriente($transaction['id_acquirer']);

            $state = $client->consultarEstadoDePago($idTransaccion);
            $lastResponse = $client->getLastResponse();
            $errors = $client->getErrors();

            $this->updateOrderTransaction($lastResponse, $order);

            $this->context->smarty->assign(array(
                'response' => $lastResponse,
                'errors' => $errors,
                'decode_lang' => $this->decodeLang,
                'rbm_state' => $state,
                'transaction' => $transaction,
                'pagaenlinea_response_header' => 'show',
            ));

            return $this->display(__FILE__, 'views/templates/admin/admin-order-content.tpl');
        }
    }

    /**
     * @param array $lastResponse
     * @param Order $order
     */
    public function updateOrderTransaction($lastResponse, Order $order)
    {
        // Update total paid
        if (!empty($lastResponse['infoPago']['montoTotal'])) {
            $currencyId = Currency::getIdByIsoCode('COP');
            $currency = new Currency($currencyId);
            $totalPaidOrder = $order->getTotalPaid($currency);
            $amountPaid = $lastResponse['infoPago']['montoTotal'];

            if ($totalPaidOrder < $amountPaid
            || !$totalPaidOrder) {
                $order->addOrderPayment(
                    $amountPaid,
                    $this->displayName,
                    $lastResponse['infoPago']['numeroAprobacion'],
                    $currency,
                    date('Y-m-d H:i:s', strtotime($lastResponse['infoPago']['fechaTransaccion']))
                );
            }
        }

        // Update order state
        if (!empty($lastResponse['infoRespuesta']['estado'])) {
            $idOrderState = (int) self::getOrderStatus($lastResponse['infoRespuesta']['estado']);
            if ($idOrderState != $order->getCurrentState()
            && !$order->hasBeenPaid()) {
                $order->setCurrentState($idOrderState);
            }
        }

        return true;
    }

    /**
     * @param array $params {
     *
     *   @var object $order
     *   @var object $customer
     *   @var array $products
     * }
     */
    public function hookDisplayAdminOrderTabOrder($params)
    {
        $order = $params['order'];
        $id_order = (int) $order->id;
        $transaction = Models_Orders::getTransactionByIdOrder($id_order);
        $idTransaccion = (int) $transaction['id_transaction'];

        if ($idTransaccion) {
            return $this->display(__FILE__, 'views/templates/admin/admin-order-tab.tpl');
        }
    }

    public function hookPaymentOptions($params)
    {
        if (!$this->active
        || !(Configuration::get('PAGAENLINEA_LIVE_MODE')
            || in_array(pSQL(Tools::getRemoteAddr()), explode(',', Configuration::get('PAGAENLINEA_TEST_IP'))))
        || !$this->checkCurrency($params['cart'])) {
            return;
        }

        $payment_options = [
            //$this->getOfflinePaymentOption(),
            $this->getExternalPaymentOption(),
            //$this->getEmbeddedPaymentOption(),
            //$this->getIframePaymentOption(),
        ];

        return $payment_options;
    }

    public function getExternalPaymentOption()
    {
        $externalOption = new PaymentOption();
        $externalOption
            ->setCallToActionText($this->l('Paga En Linea RBM'))
            ->setAction($this->context->link->getModuleLink($this->name, 'confirmation', array(), true))
            ->setInputs([
                'cart_id' => [
                    'name' => 'cart_id',
                    'type' => 'hidden',
                    'value' => (int) $this->context->cart->id,
                ],
                'secure_key' => [
                    'name' => 'secure_key',
                    'type' => 'hidden',
                    'value' => $this->context->customer->secure_key,
                ],
            ])
            ->setAdditionalInformation($this->context->smarty->fetch($this->tpls['front-help']))
            ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/logo.png'));

        return $externalOption;
    }

    public function checkCurrency(Cart $cart)
    {
        $currency_cart = new Currency($cart->id_currency);
        if (in_array($currency_cart->iso_code, $this->limited_currencies)) {
            return true;
        }

        /*
        $currencies_module = $this->getCurrency($cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_cart->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }
        */

        return false;
    }
}

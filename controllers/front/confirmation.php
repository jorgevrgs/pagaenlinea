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
class PagaenlineaConfirmationModuleFrontController extends ModuleFrontController
{
    public $content_only = true;
    public $ssl = true;
    public $display_column_left = false;
    public $display_column_right = false;
    public $auth = true;

    public function postProcess()
    {
        if ((false == Tools::getIsset('cart_id'))
        || (false == Tools::getIsset('secure_key'))) {
            Tools::redirect($this->context->link->getPageLink('order', true));
        }

        // $_GET vars
        $cart_id = (int) Tools::getValue('cart_id');
        $secure_key = pSQL(Tools::getValue('secure_key'));

        $query = array(
            'id_cart' => $cart_id,
            'secure_key' => $secure_key,
            'id_module' => $this->module->id,
        );

        // Cart object
        $cart = new Cart((int) $cart_id);
        if (!Validate::isLoadedObject($cart)) {
            throw new PrestaShopException('Cart object error');
        }

        //  Customer object
        $customer = new Customer((int) $cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            throw new PrestaShopException('Customer object error');
        }

        // Redeban API to get IdTransaccion
        $client = new Classes_RbmApi();

        if ($idTransaccion = $client->iniciarTransaccionDeCompra($cart)) {
            $query['idTransaccion'] = (int) $idTransaccion;
        }

        if (!($order_id = Order::getOrderByCartId((int) $cart->id))) {
            // You can add a comment directly into the order so the merchant will see it in the BO.
            $message = sprintf(
                $this->module->l('Payment in progress via %s with transaction Id %s'),
                $this->module->displayName,
                (int) $idTransaccion
            );

            /*
             * Converting cart into a valid order
             */
            $this->module->validateOrder(
                $cart_id,
                Configuration::get('PS_OS_BANKWIRE'),
                $cart->getOrderTotal(),
                $this->module->displayName,
                $message,
                array('transaction_id' => (int) $idTransaccion),
                (int) $cart->id_currency,
                false,
                $secure_key
            );

            /**
             * If the order has been validated we try to retrieve it.
             */
            $order_id = Order::getOrderByCartId((int) $cart->id);
        }

        if ($order_id && ($secure_key == $customer->secure_key)) {
            /*
             * The order has been placed so we redirect the customer on the validation page.
             */
            $query['id_order'] = $order_id;
        }

        Tools::redirect($this->context->link->getModuleLink($this->module->name, 'validation', $query, true));
    }
}

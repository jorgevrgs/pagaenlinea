{*
* 2018 Jorge Vargas
*
* NOTICE OF LICENSE
*
* This source file is subject to the End User License Agreement (EULA)
*
* See attachmente file LICENSE
*
* @author    Jorge Vargas <https://addons.prestashop.com/contact-form.php?id_product=31085>
* @copyright 2007-2018 Jorge Vargas
* @link      http://addons.prestashop.com/es/2_community?contributor=3167
* @license   End User License Agreement (EULA)
* @package   pagaenlinea
* @version   2.0
*}

<section id="rbm-payment-confirmation">
  {include file="module:pagaenlinea/views/templates/_partials/notifications.tpl"}
  <p>
    {l s='Your order on %s was received.' sprintf=[$shop_name] mod='pagaenlinea'}
    {l s='If you have questions, comments or concerns, please contact our' mod='pagaenlinea'}
    <a href="{$link->getPageLink('contact', true)}">
      {l s='expert customer support team.' mod='pagaenlinea'}
    </a>
  </p>
  <p>
    {l s='Reference of your order for future consultations is' mod='pagaenlinea'} <span class="reference"><strong>{$reference}</strong></span>
    {l s='with current status' mod='pagaenlinea'}
    <strong>
    {if (isset($status) == true) && ($status == 'payment')}
      {l s='COMPLETE' mod='pagaenlinea'}
    {else if (isset($status) == true) && ($status == 'canceled')}
      {l s='CANCELED' mod='pagaenlinea'}
    {else}
      {l s='ERROR' mod='pagaenlinea'}
    {/if}
    </strong>
  </p>
  <p>&nbsp;</p>
  {if isset($status) && ($status != 'canceled')}
    {include file="module:pagaenlinea/views/templates/_partials/payment-info.tpl"}
  {/if}
</section>
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

<div class="tab-pane" id="pagaenlinea">
  <h4 class="visible-print">{l s='Status' mod='pagaenlinea'}</span></h4>
  {if $errors}
    {foreach $errors as $error}
    <div class="alert alert-danger" role="alert">
      {$error|escape:'htmlall':'UTF-8'}
    </div>
    {/foreach}
  {/if}

  {if isset($transaction.is_test) && $transaction.is_test}
  <div class="alert alert-warning" role="alert">
    {l s='Transaction was registered in test mode' mod='pagaenlinea'}
  </div>
  {/if}

  {if isset($transaction.id_transaction) && $transaction.id_transaction}
  <div class="alert alert-info" role="alert">
    {l s='Transaction Id:' mod='pagaenlinea'}: {$transaction.id_transaction}
  </div>
  {/if}

  <div class="panel">
    {include file="./../_partials/payment-info.tpl"}
  </div>
</div>
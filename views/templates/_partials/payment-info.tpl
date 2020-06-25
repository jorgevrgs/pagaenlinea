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

<table class="table">
  <thead>
    <tr>
      <th>{l s='Description' mod='pagaenlinea'}</th>
      <th>{l s='Value' mod='pagaenlinea'}</th>
    </tr>
  </thead>

  <tbody>
    {if isset($response.cabeceraRespuesta.infoPuntoInteraccion) && isset($pagaenlinea_response_header) && $pagaenlinea_response_header == 'show'}
      <tr>
        <td colspan="2"><strong>{l s='Request header' mod='pagaenlinea'}</strong></td>
      </tr>
      {foreach $response.cabeceraRespuesta.infoPuntoInteraccion as $key => $value}
      <tr>
        <td class="cabeceraRespuesta-infoPuntoInteraccion-{$key}">
          <strong>
            {if isset($decode_lang.$key)}
              {$decode_lang.$key}
            {else}
              {$key}
            {/if}
          </strong>
        </td>
        <td>
          {$value}
        </td>
      </tr>
      {/foreach}
    {/if}

    {if isset($response.infoPago)}
      <tr>
        <td colspan="2"><strong>{l s='Payment info' mod='pagaenlinea'}</strong></td>
      </tr>
      {foreach $response.infoPago as $key => $value}
      <tr>
        <td class="infoPago-{$key}">
          <strong>
            {if isset($decode_lang.$key)}
              {$decode_lang.$key}
            {else}
              {$key}
            {/if}
          </strong>
        </td>
        <td>
          {$value}
        </td>
      </tr>
      {/foreach}
    {/if}
  </tbody>
  

  {if isset($response.infoRespuesta)}
  <tfoot>
    <tr>
      <td colspan="2"><strong>{l s='Response info' mod='pagaenlinea'}</strong></td>
    </tr>
    {foreach $response.infoRespuesta as $key => $value}
    <tr>
      <td class="infoRespuesta-{$key}">
      <strong>
        {if isset($decode_lang.$key)}
          {$decode_lang.$key}
        {else}
          {$key}
        {/if}
      </strong>
      </td>
      <td>{$value}</td>
    </tr>
    {/foreach}
  </tfoot>
  {/if}
</table>
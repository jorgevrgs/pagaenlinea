{**
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

<aside id="notifications">
  <div class="box">

    {if !empty($errors)}
        <article class="alert alert-danger" role="alert" data-alert="danger">
          <ul>
            {foreach $errors as $notif}
              <li>{$notif}</li>
            {/foreach}
          </ul>
        </article>
    {/if}

    {if !empty($warnings)}
        <article class="alert alert-warning" role="alert" data-alert="warning">
          <ul>
            {foreach $warnings as $notif}
              <li>{$notif}</li>
            {/foreach}
          </ul>
        </article>
    {/if}

    {if !empty($successes)}
        <article class="alert alert-success" role="alert" data-alert="success">
          <ul>
            {foreach successes as $notif}
              <li>{$notif}</li>
            {/foreach}
          </ul>
        </article>
    {/if}

    {if !empty($infos)}
        <article class="alert alert-info" role="alert" data-alert="info">
          <ul>
            {foreach $infos as $notif}
              <li>{$notif}</li>
            {/foreach}
          </ul>
        </article>
    {/if}
  </div>
</aside>
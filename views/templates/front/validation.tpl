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

{extends file='checkout/checkout.tpl'}

{block name='page_title'}
    {l s='Payment Validation' d='Modules.PagaEnLinea' mod='pagaenlinea'}
{/block}

{block name='notifications'}
  {include file="module:pagaenlinea/views/templates/_partials/notifications.tpl"}
{/block}

{block name='content'}
  <section id="content">
    <ul>
        <!-- Button URL -->
        <li class="alert alert-info" role="alert">
        <i id="step-1" class="hidden"></i> {l s='Step 1: Open Paga En Linea window.' mod='pagaenlinea'}
        {if !empty($buttonUrl)}
            <a
            class="alert-link"
            id="redeban"
            href="{$buttonUrl|escape:'htmlall':'UTF-8'}"
            data-width="712"
            data-height="800"
            data-mobile="{if isset($browser) && $browser->isMobile()}true{else}false{/if}"
            >
                <em>({l s='If window don\'t open automatically click here...' mod='pagaenlinea'})</em>
            </a>
        {/if}
        </li>
        <!-- Redirect URL -->
        <li class="alert alert-info" role="alert">
        <i id="step-2" class="hidden"></i> {l s='Step 2: Redirect to order confirmation page.' mod='pagaenlinea'}
        {if !empty($redirectUrl)}
            <a class="alert-link" id="redirect" href="{$redirectUrl|escape:'htmlall':'UTF-8'}">
                <em>({l s='If window don\'t redirect automatically click here...' mod='pagaenlinea'})</em>
            </a>
        {/if}
        </li>
    </ul>
  </section>
{/block}

{block name='javascript_bottom'}
  {$smarty.block.parent}
  {if !empty($buttonUrl)}
  <script type="text/javascript">
  "use strict";
  $(document).ready(function() {
      var windowOpen, childWindow, interval, message, origin;
      var urlButton = $("#redeban").attr("href"),
      urlRedirect = $("#redirect").attr("href"),
      isMobile = ($("#redeban").attr("data-mobile") === "true" ? true : false);

      /**
      * FUNCTIONS GENERAL
      */
      var windowRedirect = function(url) {
          if (url === undefined) {
              url = urlRedirect;
          }

          $("#step-2").toggleClass("hidden").addClass("fa fa-check");
          window.open(url, "_self");
      };

      /**
       * FUNCTIONS POPUP
       */
      $("#redeban").click(function(e) {
          e.preventDefault();

          // Detect child window closed
          function closeCallback() {
              windowRedirect(urlRedirect);
          }

          // Open new window and ask for close
          windowOpen = function(url, title, options, closeCallback) {
              if (url === undefined) {
                  url = urlButton;
              }
              if (title === undefined) {
                  title = "_blank";
              }
              if (options === undefined) {
                  options = "";
              }

              childWindow = window.open(url, title, options);
              interval = window.setInterval(function() {
                  try {
                      if (childWindow == null || childWindow.closed) {
                          window.clearInterval(interval);
                          closeCallback();
                      }
                  }
                  catch (e) {
                  }
              }, 1000);

              return childWindow;
          };

          childWindow = windowOpen(urlButton, "_blank", "menubar=0,width=712,height=800", closeCallback);
      });

      if (isMobile === true) {
          // Use popup
          $("#redeban").first().trigger('click');
      } else {
          // use Fancybox
          $.fancybox.open({
              src: urlButton,
              type: 'iframe',
              opts: {
                  closeBtn: true,
                  closeClickOutside: false,
                  fullScreen: false,
                  iframe: {
                      scrolling: 'yes',
                      css: {
                          width : '712px'
                      }
                  },
                  afterClose: function() {
                      windowRedirect(urlRedirect);
                  }
              }
          });
      }

      /**
       * EVENT MANAGER
       */
      $(window).on("message onmessage", function(e) {
          message = e.originalEvent.data;
          origin = e.originalEvent.origin;

          if (origin === "https://www.pagosrbm.com" || origin === "https://www.pagaenlinearbm.com") {
              // Alert message
              $("#step-1").toggleClass("hidden");
              if ("Finalizacion" == message) {
                  $("#step-1").addClass("fa fa-check");
              } else if ("Error" == message) {
                  $("#step-1").addClass("fa fa-exclamation-triangle");
              } else if ("Cancelacion" == message) {
                  $("#step-1").addClass("fa fa-ban");
              }

              // Popup close
              if (typeof childWindow !== "undefined") {
                  childWindow.close();
              } else {
                  childWindow = e.originalEvent.source;
                  childWindow.close();
              }

              // Fancybox close
              $.fancybox.getInstance().close();
          }
      });

    
  });
  </script>
  <noscript>
      <div class="alert alert-warning" role="alert">
          {l s='Your browser does not support JavaScript! Please use Chrome, Safari, Opera or Mozilla Firefox.' mod='pagaenlinea'}
      </div>
  </noscript>
  {/if}
{/block}
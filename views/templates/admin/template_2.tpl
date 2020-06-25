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

<div class="panel">
	<div class="row pagaenlinea-header">
		<img src="{$module_dir|escape:'html':'UTF-8'}views/img/paga_linea_RBM.png" class="col-xs-6 col-md-3 text-center" id="payment-logo" />
		<div class="col-xs-6 col-md-9 text-center text-muted">
			{l s='Paga En Linea Boton de Pagos Module allows you to connect your prestashop store to Redeban Multicolor
      through your webservice and receive credit cards directly in your store through a Lightbox without the customer
      leaving your store' mod='pagaenlinea'}
		</div>
	</div>

	<hr />
	
	<div class="pagaenlinea-content">
		<div class="row">
			<div class="col-md-6">
				<h5>{l s='Benefits of using Paga En Linea Boton de Pagos module' mod='pagaenlinea'}</h5>
				<ul class="ul-spaced">
					<li>
						<strong>{l s='Acceptance' mod='pagaenlinea'}:</strong>
						{l s='Accept franchise credit cards and also private cards. A prior affiliation to each franchise is required.' mod='pagaenlinea'}
					</li>
					
					<li>
						<strong>{l s='Growth' mod='pagaenlinea'}:</strong>
						{l s='Get competitive costs so your business grows faster.' mod='pagaenlinea'}
					</li>
					
					<li>
						<strong>{l s='Connectivity' mod='pagaenlinea'}:</strong>
						{l s='Integrate these solutions easily to your virtual store or to your own systems.' mod='pagaenlinea'}
					</li>
					
					<li>
						<strong>{l s='Availability' mod='pagaenlinea'}:</strong>
						{l s='Consulte en línea los reportes de todas sus transacciones.' mod='pagaenlinea'}
					</li>

					<li>
						<strong>{l s='Security' mod='pagaenlinea'}:</strong>
						{l s='Redeban Multicolor performs transaction monitoring for greater security.' mod='pagaenlinea'}
					</li>

					<li>
						<strong>{l s='Disepersion' mod='pagaenlinea'}:</strong>
						{l s='Realize dispersion of funds if you have a trade that sells to third parties' mod='pagaenlinea'}
					</li>
				</ul>
			</div>
			
			<div class="col-md-6">
				<h5>{l s='How does it work?' mod='pagaenlinea'}</h5>
                <iframe width="560" height="315" src="https://www.youtube.com/embed/EDQDomIH4LU" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
			</div>
		</div>

		<hr />
		
		<div class="row">
			<div class="col-md-12">
				<p class="text-muted">{l s='Start accepting franchise and private credit cards in your virtual store:' mod='pagaenlinea'}</p>
				
				<div class="row">
					<img src="{$module_dir|escape:'html':'UTF-8'}views/img/cards.jpg" class="col-md-3" id="payment-logo" />
					<div class="col-md-9 text-center">
						<h6>{l s='For more information, call +57 1 3323200 in Bogota DC.' mod='pagaenlinea'} {l s='or' mod='pagaenlinea'} <a href="http://www.pagaenlinearbm.com.co/">http://www.pagaenlinearbm.com.co/</a></h6>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="panel">
	<p class="text-muted">
		<i class="icon icon-info-circle"></i> {l s='In order to create a secure account with My Payment Module, please complete the fields in the settings panel below:' mod='pagaenlinea'}
		{l s='By clicking the "Save" button you are creating secure connection details to your store.' mod='pagaenlinea'}
		{l s='My Payment Module signup only begins when you client on "Activate your account" in the registration panel below.' mod='pagaenlinea'}
		{l s='If you already have an account you can create a new shop within your account.' mod='pagaenlinea'}
	</p>
	<p>
		<a href="#" onclick="javascript:return false;"><i class="icon icon-file"></i> Link to the documentation</a>
	</p>
</div>
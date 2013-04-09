<?php
/**
* @copyright	Copyright (C) 2009 - 2009 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
* @ref 			https://www.x.com/docs/DOC-1332#id08A6HI00JQU
*/
if(defined('_JEXEC')===false) die();?>
<script>
window.onload = function() 
{	
	  setTimeout("paypalSubmit()", 2000);
}

function paypalSubmit()
{
	//document.forms["site_app_<?php echo $this->getName(); ?>_form"].submit();
}
</script>


<form action="<?php echo $post_url ?>"
	  method="post" name="site_app_<?php echo $this->getName(); ?>_form" >
	
	<?php 
		curl_exec($ch);
		curl_close($ch); ?>

	
	<!--First Data INFO
    txntype<input type='text' name='txntype' value='<?php echo $txntype;?>' />
    timezone<input type='text' name='timezone' value='<?php echo $timezone;?>' />
    txndatetime<input type='text' name='txndatetime' value='<?php echo $txndatetime;?>' />
    hash<input type='text' name='hash' value='<?php echo $hash;?>' />
    storename<input type='text' name='storename' value='<?php echo $storename;?>' />
    mode<input type='text' name='mode' value='<?php echo $mode;?>' />
    total<input type='text' name='chargetotal' value='<?php echo $chargetotal;?>' />
  
    subtotal<input type='text' name='subtotal' value='<?php echo $subtotal;?>' />
    trxOrigin<input type="text" name="trxOrigin" value='<?php echo $trxOrigin;?>' />
     
    PaymentMethod<input type="text" name="paymentMethod" value='<?php echo $paymentMethod;?>' />
	 -->
 
	<!--ORDER INFO
    <input type='hidden' name='app_id'		value='<?php echo $this->getId();?>' />
	<input type='hidden' name='order_id' 	value='<?php echo $order_id;?>' />
	<input type='hidden' name='invoice' 	value='<?php echo $invoice; ?>'>
	<input type='hidden' name='item_name' 	value='<?php echo $item_name;?>'>
	<input type='hidden' name='item_number' value='<?php echo $item_number; ?>'>

	<input type='hidden' name='responseSuccessURL' 			value='<?php echo $responseSuccessURL; ?>'>
	<input type='hidden' name='responseFailURL' 	value='<?php echo $responseFailURL; ?>'>
	<input type="hidden" name="notify_url" 		value="<?php echo $notify_url; ?>" />
-->
 
	
	<div id="payment-paypal" class="pp-payment-pay-process">		
		<div id="payment-redirection">
			<div class="pp-message pp-bold">
				<?php echo XiText::_('COM_PAYPLANS_APP_PAYPAL_PAYMENT_REDIRECTION'); ?>
			</div>
			
			<div class=""></div>
		</div>
	
		<div id="payment-submit" class="pp-gap-top20">
			<button type="submit" class="pp-button ui-button-primary ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only"
					name="payplans_payment_btn"><?php echo XiText::_('COM_PAYPLANS_PAYPAL_PAYMENT')?></button>
		</div>
	</div>
</form>


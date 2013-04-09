<?php
/**
* @package		PayPlans
* @subpackage	Frontend
* @contact 		chris.zietlow@morris.com
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


  <form action="<?php echo $post_url ?>"  method="post" name="site_app_<?php echo $this->getName(); ?>_form" >
	
<!-- <form action="https://connect.merchanttest.firstdataglobalgateway.com/IPGConnect/gateway/processing" -->
	 
	<?php 
	if ($identifier == TRUE) {
		curl_exec($ch);
		curl_close($ch); 
	}
	?>

	
	<!--First Data INFO -->
		
    txntype<input type='text' name='txntype' value='<?php echo $postFields['txntype'];?>' />
    timezone<input type='text' name='timezone' value='<?php echo $postFields['timezone'];?>' />
    txndatetime<input type='text' name='txndatetime' value='<?php echo $postFields['txndatetime'];?>' />
    hash<input type='text' name='hash' value='<?php echo $postFields['hash'];?>' />
    storename<input type='text' name='storename' value='<?php echo $postFields['storename'];?>' />
    mode<input type='text' name='mode' value='<?php echo $postFields['mode'];?>' />
    total<input type='text' name='chargetotal' value='<?php echo $postFields['chargetotal'];?>' />
  
    subtotal<input type='text' name='subtotal' value='<?php echo $postFields['subtotal'];?>' />
    trxOrigin<input type="text" name="trxOrigin" value='<?php echo $postFields['trxOrigin'];?>' />
     
 
 <!--  
  <input type='hidden' name='app_id'		value='<?php echo $this->getId();?>' />
	<input type='hidden' name='order_id' 	value='<?php echo $order_id;?>' />
	<input type='hidden' name='invoice' 	value='<?php echo $invoice; ?>'>
	<input type='hidden' name='item_name' 	value='<?php echo $item_name;?>'>
	<input type='hidden' name='item_number' value='<?php echo $item_number; ?>'>
-->

<label>Payment Method</label><select size="1" name="paymentMethod">
	<OPTION value=V>Visa</OPTION>
	<OPTION value=M>MasterCard</OPTION>
	<OPTION value=A>American Express</OPTION> 
	<OPTION value=D>Discover</OPTION> 
	<OPTION value=J>JCB</OPTION>
	<OPTION value=9>Check</OPTION>
	<OPTION value="">Other</OPTION>
 </select>
 <label>Card Number</label><input type='text' name='cardnumber' />
 <label>Expiration Month</label><input type='text' name='expmonth' />
 <label>Expiration Year</label><input type='text' name='expyear' />

 <input type="hidden" name="identifier" value="TRUE" />
	<div id="payment-paypal" class="pp-payment-pay-process">		
		<div id="payment-redirection">
			<div class="pp-message pp-bold">
				<?php //echo XiText::_('COM_PAYPLANS_APP_PAYPAL_PAYMENT_REDIRECTION'); ?>
			</div>
			
			<div class=""></div>
		</div>
	
		<div id="payment-submit" class="pp-gap-top20">
			<button type="submit" class="pp-button ui-button-primary ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only"
					name="payplans_payment_btn">Payment</button>
		</div>
	</div>
</form>


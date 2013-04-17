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
// 	  setTimeout("paypalSubmit()", 2000);
}

function paypalSubmit()
{
	//document.forms["site_app_<?php echo $this->getName(); ?>_form"].submit();
}
</script>


  <form action="<?php echo $post_url ?>"  method="post" name="site_app_<?php echo $this->getName(); ?>_form" >
	
<!-- <form action="https://connect.merchanttest.firstdataglobalgateway.com/IPGConnect/gateway/processing" -->
	 


	
	<!--First Data INFO -->
		
    <input type='hidden' name='txntype' value='<?php echo $postFields['txntype'];?>' />
    <input type='hidden' name='timezone' value='<?php echo $postFields['timezone'];?>' />
    <input type='hidden' name='txndatetime' value='<?php echo $postFields['txndatetime'];?>' />
    <input type='hidden' name='hash' value='<?php echo $postFields['hash'];?>' />
    <input type='hidden' name='storename' value='<?php echo $postFields['storename'];?>' />
    <input type='hidden' name='mode' value='<?php echo $postFields['mode'];?>' />
    <input type='hidden' name='chargetotal' value='<?php echo $postFields['chargetotal'];?>' />
  
    <input type='hidden' name='subtotal' value='<?php echo $postFields['subtotal'];?>' />
    <input type='hidden' name="trxOrigin" value='<?php echo $postFields['trxOrigin'];?>' />
     
 
 <!-- payplans info
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


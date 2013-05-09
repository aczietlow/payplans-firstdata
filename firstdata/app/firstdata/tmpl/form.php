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
	
	<label>Name<input type='text' name='name' /></label>
	
	<label>Payment Method<select class="payment_method" size="1" name="paymentMethod">
		<OPTION value=V>Visa</OPTION>
		<OPTION value=M>MasterCard</OPTION>
		<OPTION value=A>American Express</OPTION> 
		<OPTION value=D>Discover</OPTION> 
	</select>
	</label>
	
	<label>Card Number<input type='text' name='cardnumber' /></label>
	
	 <div class="cc_date_label">Expiration Date</div><select size="1" class="cc_date cc_date_month" name="expmonth">
		<OPTION value=1>1</OPTION>
		<OPTION value=2>2</OPTION>
		<OPTION value=3>3</OPTION> 
		<OPTION value=4>4</OPTION> 
		<OPTION value=5>5</OPTION>
		<OPTION value=6>6</OPTION>
		<OPTION value=7>7</OPTION> 
		<OPTION value=8>8</OPTION> 
		<OPTION value=9>9</OPTION>
		<OPTION value=10>10</OPTION>
		<OPTION value=11>11</OPTION> 
		<OPTION value=12>12</OPTION> 
	</select>
	
		<select size="1" class="cc_date cc_data_year"  name="expyear">
		<OPTION value=2013>2013</OPTION>
		<OPTION value=2014>2014</OPTION>
		<OPTION value=2015>2015</OPTION> 
		<OPTION value=2016>2016</OPTION> 
		<OPTION value=2017>2017</OPTION>
		<OPTION value=2018>2018</OPTION>
		<OPTION value=2019>2019</OPTION> 
		<OPTION value=2020>2020</OPTION> 
	</select>
	
	
	<input type="hidden" name="identifier" value="TRUE" />
	
	<div id="payment-paypal" class="pp-payment-pay-process">		
		<div id="payment-submit" class="pp-gap-top20">
			<button type="submit" name="payplans_payment_btn">Payment</button>
		</div>
	</div>
</form>


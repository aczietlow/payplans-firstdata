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

<!-- Add descriptions for erros  -->

<form action="<?php echo $post_url ?>"  method="post" name="site_app_<?php echo $this->getName(); ?>_form" >
	<div class="personalInfo">
		<!-- Personal Info (Full PayPlus Mode) -->
		<div id="name-description"></div>
		<div class='firstdataInput'>
			<label>Full Name *<input type='text' name='bname' data-required data-pattern="^([A-z]*((\s)))+[A-z]*$"  data-describedby="name-description" data-description="name" /></label>
		</div>
		
		<div class='firstdataInput'>
			<label>Address *<input type="text" name='baddr1' data-required /></label>
		</div>
		
		<div class='firstdataInput'>
			<label>City *<input type="text" name='bcity' data-required/></label>
		</div>
		
		<div class='firstdataInput'>
		  <label>Country <select name='bcountry' data-required>
		    <option value="US"> United States </option>
		    <option value="CA"> Canada </option>
		  </select>
		  </label>
		</div>
		
		<div class='firstdataInput'>
			<label>State *<select size="1" name="bstate" >
				<option value = "AL"> Alabama </option>
		    <option value = "AK"> Alaska </option>
		    <option value = "AR"> Arkansas </option>
		    <option value = "AZ"> Arizona </option>
		    <option value = "CA"> California </option>
				<option value = "CT"> Connecticut </option>
				<option value = "DE"> Delaware </option>
				<option value = "FL"> Flordia </option>
				<option value = "GA"> Georgia </option>
				<option value = "HI"> Hawaii </option>
				<option value = "ID"> Idaho </option>
				<option value = "IL"> Illinois </option>
				<option value = "IN"> Indiana </option>
				<option value = "IA"> Iowa </option>
				<option value = "KS"> Kansas </option>
				<option value = "KY"> Kentucky </option>
				<option value = "LA"> Louisiana </option>
				<option value = "ME"> Maine </option>
				<option value = "MD"> Maryland </option>
				<option value = "MA"> Masschusetts </option>
				<option value = "MI"> Michigan </option>
				<option value = "MN"> Minnesota </option>
				<option value = "MS"> Mississippi </option>
				<option value = "MO"> Missouri </option>
				<option value = "MT"> Montana </option>
				<option value = "NE"> Nebraska </option>
				<option value = "NV"> Nevada </option>
				<option value = "NH"> New Hampshire </option>
				<option value = "NJ"> New Jersey </option>
				<option value = "NM"> New Mexico </option>
				<option value = "NY"> New York </option>
				<option value = "NC"> North Carolina </option>
				<option value = "ND"> North Dakota </option>
				<option value = "OH"> Ohio </option>
				<option value = "OK"> Oklahoma </option>
				<option value = "OR"> Oregon </option>
				<option value = "PA"> Pennsylvania </option>
				<option value = "RI"> Rhode Island </option>
				<option value = "SC"> South Carolina </option>
				<option value = "SD"> South Dakota </option>
				<option value = "TN"> Tennessee </option>
				<option value = "TX"> Texas </option>
				<option value = "UT"> Utah </option>
				<option value = "VT"> Vermont </option>
				<option value = "VA"> Virginia </option>
				<option value = "WA"> Washington </option>
				<option value = "WV"> West Virginia </option>
				<option value = "WI"> Wisconsin </option>
				<option value = "WY"> Wyoming </option>
				<option value = "PR"> Puerto Rico </option>
				<option value = "AB"> Alberta </option>
        <option value = "BC">British Columbia</option>
        <option value = "MB">Manitoba</option>
        <option value = "NB">New Brunswick</option>
        <option value = "NF">Newfoundland</option>
        <option value = "NS">Nova Scotia</option>
        <option value = "NT">Northwest Territories</option>
        <option value = "NU">Nunvut</option>
        <option value = "ON">Ontario</option>
        <option value = "PE">Prince Edward Island</option>
        <option value = "QC">Quebec</option>
        <option value = "NS">Nova Sc</option>
        <option value = "SK">Saskatchewan</option>
        <option value = "YT">Yukon Territory</option>
			</select>
			</label>
		</div>
		
		
		<div class='firstdataInput'>
			<label>Zip *<input type="text" name='bzip' data-required data-pattern="\d{5}(-\d{4})?" /></label>
		</div>
		
		<div class='firstdataInput'>
			<label>Phone *<input type="text" name='phone' data-required data-pattern="((\(\d{3}\) ?)|(\d{3}-))?\d{3}-?\d{4}"  /></label>
		</div>
		
	</div>
	
	<div class="ccInfo">
		<!-- NBHA specific information -->
		
		<!-- CC information (Pay Only) -->
		<div class='firstdataInput'>
			<label>Payment Method *<select class="payment_method" size="1" name="paymentMethod">
				<OPTION value=V>Visa</OPTION>
				<OPTION value=M>MasterCard</OPTION>
				<OPTION value=A>American Express</OPTION> 
				<OPTION value=D>Discover</OPTION> 
			</select>
			</label>
		</div>
		
		<div class='firstdataInput'>
			<label>Card Number *<input type='text' name='cardnumber' data-required /></label>
		</div>
		
			<div class="cc_date_label">Expiration Date *</div><select size="1" class="cc_date cc_date_month" name="expmonth">
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
		
			<select size="1" class="cc_date cc_date_year"  name="expyear">
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
		<div id="payment-submit" class="pp-gap-top20">
			<button type="submit" name="payplans_payment_btn">Pay Now</button>
		</div>
	</div>
</form>


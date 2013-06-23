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
// 	document.forms["resForm"].submit();
// 	document.forms[1].submit();
// 	document.getElementById('resForm').hide();
}
</script>
	<?php
	if ($identifier == TRUE && empty($_POST['status'])) {
    
    $ch = curl_init();
    	
    curl_setopt_array($ch, array(
    CURLOPT_URL => $url,
    CURLOPT_FOLLOWLOCATION => TRUE,
    CURLOPT_POST => TRUE,
    CURLOPT_POSTFIELDS => $postFields,
    CURLOPT_REFERER => $referer,
    CURLOPT_SSL_VERIFYPEER => FALSE,));		
    curl_exec($ch);
		curl_close($ch); 
	}
	?>



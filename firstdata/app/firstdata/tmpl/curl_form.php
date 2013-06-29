<?php
/**
* @package		PayPlans
* @subpackage	Frontend
* @contact 		chris.zietlow@morris.com
*/
if(defined('_JEXEC')===false) die();?>
<script type="text/javascript">
jQuery(document).ready(function() {
	tranStatus = $('input[name="r_approved"]').val();
	
	//successful transaction
	if (tranStatus == "APPROVED") {
		$('input[name="Checkout2"]').trigger('click');
	}

});

</script>


	<?php
	//using for debugging only
	
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



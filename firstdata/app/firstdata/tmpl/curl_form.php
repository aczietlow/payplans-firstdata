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
// 		curl_exec($ch);
// 		curl_close($ch); 
  include_once '../Krumo/class.krumo.php';
//    foreach ($args as $key => $value) {
    krumo($args);
// 	}
	}
	?>



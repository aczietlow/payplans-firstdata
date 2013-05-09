jQuery(document).ready(function() {
	jQuery('#date').datepicker();
	
	jQuery("form[name='site_app_firstdata_form']").validate({
		onKeyup : true,
		eachValidField : function() {

			jQuery(this).closest('div.firstdataInput').removeClass('error').addClass('success');
		},
		eachInvalidField : function() {

			jQuery(this).closest('div.firstdataInput').removeClass('success').addClass('error');
		}
	});
});

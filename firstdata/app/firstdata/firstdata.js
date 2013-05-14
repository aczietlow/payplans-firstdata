/**
 * TODO: Add validation to jquery date select ui and dropdowns
 */

jQuery(document).ready(function() {
	//add datapicker width to dob field
	jQuery('#date').datepicker();
	
	//set all select inputs to a blank value.
	jQuery("form[name='site_app_firstdata_form'] select").prop('selectedIndex', -1);	
	
	//add validate functionality to the input fields
	jQuery("form[name='site_app_firstdata_form']").validate({
		onChange : true,
		eachValidField : function() {
			jQuery(this).closest('div.firstdataInput').removeClass('error').addClass('success');
		},
		eachInvalidField : function() {
			jQuery(this).closest('div.firstdataInput').removeClass('success').addClass('error');
		},
//		description: {
//			name: {
//				required: '<div class="alert alert-error">Required</div>',
//				pattern: '<div class="alert alert-error">Ex: John Smith</div>'
//			}
//		}
		
		
	});
});

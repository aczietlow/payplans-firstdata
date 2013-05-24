/**
 * TODO: Add validation to jquery date select ui and dropdowns
 */

jQuery(document).ready(function() {
	//add datapicker width to dob field
	
	//set all select inputs to a blank value.
	$("form[name='site_app_firstdata_form'] select").prop('selectedIndex', -1);	
	
	//add validate functionality to the input fields
	$("form[name='site_app_firstdata_form']").validate({
		onChange : true,
		eachValidField : function() {
			$(this).closest('div.firstdataInput').removeClass('error').addClass('success');
		},
		eachInvalidField : function() {
			$(this).closest('div.firstdataInput').removeClass('success').addClass('error');
		},
//		description: {
//			name: {
//				required: '<div class="alert alert-error">Required</div>',
//				pattern: '<div class="alert alert-error">Ex: John Smith</div>'
//			}
//		}
		
		
	});
});

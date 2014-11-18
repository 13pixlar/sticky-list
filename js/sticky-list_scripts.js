jQuery(document).ready(function($) {

	// Define some variables
	var siblings 			= $('#gaddon-setting-row-enable_list').siblings('[id^=gaddon-setting-row]');
	var active 				= $('#enable_list');
	var enableView 			= $('#enable_view');
	var enableViewLabel 	= $('#gaddon-setting-row-enable_view_label');
	var enableEdit 			= $('#enable_edit');
	var enableEditLabel 	= $('#gaddon-setting-row-enable_edit_label');
	var updateText			= $('#gaddon-setting-row-update_text')
	var enableDelete		= $('#enable_delete');
	var enableDeleteLabel 	= $('#gaddon-setting-row-enable_delete_label');

	
	/**
     * Main function to toggle fields depending on checkboxes
     *
     */
	function toggleActive() {

		if(active.is(':checked')) {
	        siblings.addClass('show');
	    
	        if(enableView.is(':checked')) {
	        	enableViewLabel.addClass('show');
		    }else{
		    	enableViewLabel.removeClass('show');
		    }
		    if(enableEdit.is(':checked')) {
		        enableEditLabel.addClass('show');
		        updateText.addClass('show');
		    }else{
		    	enableEditLabel.removeClass('show');
		    	updateText.removeClass('show');
		    }
		    if(enableDelete.is(':checked')) {
		        enableDeleteLabel.addClass('show');
		    }else{
		    	enableDeleteLabel.removeClass('show');
		    }
	    
	    }else{
	    	siblings.removeClass('show');
	    }
	}

	// Run the function on page load
	toggleActive();

	// Toggle visibility on click
	
	active.click(function(event) {
		toggleActive();
	});

	enableView.click(function(event) {
		enableViewLabel.toggleClass('show');
	});

    enableEdit.click(function(event) {
		enableEditLabel.toggleClass('show');
		updateText.toggleClass('show');
	});

    enableDelete.click(function(event) {
		enableDeleteLabel.toggleClass('show');
	});	
});
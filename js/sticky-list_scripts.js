jQuery(document).ready(function($) {

	// Helper function to make headers
    function settingsHeader(nr,text) {
        return '<tr id="gaddon-setting-row-header-'+nr+'" class="show"><td colspan="2"><h4 class="gf_settings_subgroup_title">'+text+'</h4></td></tr>';
    }

	$('#gaddon-setting-row-enable_list').after(settingsHeader('0',''));
    $('#gaddon-setting-row-enable_view').before(settingsHeader('1',''));
    $('#gaddon-setting-row-action_column_header').before(settingsHeader('2',''));
    $('#gaddon-setting-row-enable_sort').before(settingsHeader('3',''));

    // Add donate info
    $('#gaddon-setting-row-enable_search').after(settingsHeader('4',''));
    $('#gaddon-setting-row-header-4').after('<tr id="gaddon-setting-row-donate" class="show"><td class="donate-text" colspan="2"></td></tr>');

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
	var deleteType		 	= $('#gaddon-setting-row-delete_type');
	var enableSort			= $('#enable_sort');
	var enableSearch		= $('#gaddon-setting-row-enable_search');

	
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
		        deleteType.addClass('show');
		    }else{
		    	enableDeleteLabel.removeClass('show');
		    	deleteType.removeClass('show');
		    }
		    if(enableSort.is(':checked')) {
		        enableSearch.addClass('show');
		    }else{
		    	enableSearch.removeClass('show');
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
		deleteType.toggleClass('show')
	});

	enableSort.click(function(event) {
		enableSearch.toggleClass('show');
		
	});
});
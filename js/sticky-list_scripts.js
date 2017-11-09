jQuery(document).ready(function($) {

	// Helper function to make headers
    function settingsHeader(nr,text) {
        return '<tr id="gaddon-setting-row-header-'+nr+'" class="show"><td colspan="2"><h4 class="gf_settings_subgroup_title">'+text+'</h4></td></tr>';
    }

	$('#gaddon-setting-row-enable_list').after(settingsHeader('0',''));
    $('#gaddon-setting-row-enable_view').before(settingsHeader('1',''));
    $('#gaddon-setting-row-action_column_header').before(settingsHeader('2',''));
    $('#gaddon-setting-row-enable_sort').before(settingsHeader('3',''));
    $('#gaddon-setting-row-enable_pagination').before(settingsHeader('4',''));

    // Add donate info
    $('#gaddon-setting-row-page_entries').after(settingsHeader('5',''));
    $('#gaddon-setting-row-header-5').after('<tr id="gaddon-setting-row-donate" class="show"><td class="donate-text" colspan="2"></td></tr>');

	// Define some variables
	var siblings 				= $('#gaddon-setting-row-enable_list').siblings('[id^=gaddon-setting-row]');
	var active 					= $('#enable_list');
	var enablePostLink			= $('#enable_postlink');
	var enablePostLinkLabel		= $('#gaddon-setting-row-link_label');
	var enableView 				= $('#enable_view');
	var enableViewLabel 		= $('#gaddon-setting-row-enable_view_label');
	var enableEdit 				= $('#enable_edit');
	var newEntryId 				= $('#gaddon-setting-row-new_entry_id');
	var enableEditLabel 		= $('#gaddon-setting-row-enable_edit_label');
	var updateText				= $('#gaddon-setting-row-update_text')
	var enableDelete			= $('#enable_delete');
	var enableDeleteLabel 		= $('#gaddon-setting-row-enable_delete_label');
	var confirmDelete			= $('#gaddon-setting-row-confirm_delete');
	var confirmDeleteCheckbox	= $('#confirm_delete');
	var confirmDeleteText		= $('#gaddon-setting-row-confirm_delete_text');
	var enableDuplicate			= $('#enable_duplicate');
	var enableDuplicateLabel 	= $('#gaddon-setting-row-enable_duplicate_label');
	var deleteType		 		= $('#gaddon-setting-row-delete_type');
	var enablePdf				= $('#enable_pdf');
	var enablePdfLabel			= $('#gaddon-setting-row-pdf_label');
	var enablePdfId				= $('#gaddon-setting-row-pdf_id');
	var enableSort				= $('#enable_sort');
	var initialSort				= $('#gaddon-setting-row-initial_sort');
	var initialSortDirection 	= $('#gaddon-setting-row-initial_sort_direction');
	var enableSearch			= $('#gaddon-setting-row-enable_search');
	var enablePagination		= $('#enable_pagination');
	var pageEntries				= $('#gaddon-setting-row-page_entries');


	/**
     * Main function to toggle fields depending on checkboxes
     *
     */
	function toggleActive() {

		if(active.is(':checked')) {
	        siblings.addClass('show');

	        if(enablePostLink.is(':checked')) {
	        	enablePostLinkLabel.addClass('show');
		    }else{
		    	enablePostLinkLabel.removeClass('show');
		    }
	        if(enableView.is(':checked')) {
	        	enableViewLabel.addClass('show');
		    }else{
		    	enableViewLabel.removeClass('show');
		    }
		    if(enableEdit.is(':checked')) {
		        enableEditLabel.addClass('show');
		        updateText.addClass('show');
		        newEntryId.addClass('show');
		    }else{
		    	enableEditLabel.removeClass('show');
		    	updateText.removeClass('show');
		    	newEntryId.removeClass('show');
		    }
		    if(enableDelete.is(':checked')) {
		        enableDeleteLabel.addClass('show');
		        confirmDelete.addClass('show');
		        deleteType.addClass('show');
		    }else{
		    	enableDeleteLabel.removeClass('show');
		    	confirmDelete.removeClass('show');
		    	deleteType.removeClass('show');
		    }
		    if(confirmDeleteCheckbox.is(':checked') && enableDelete.is(':checked')) {
		        confirmDeleteText.addClass('show');
		    }else{
				confirmDeleteText.removeClass('show');
		    }
		    if(enableDuplicate.is(':checked')) {
	        	enableDuplicateLabel.addClass('show');
		    }else{
		    	enableDuplicateLabel.removeClass('show');
		    }
			if(enablePdf.is(':checked')) {
				enablePdfLabel.addClass('show');
				enablePdfId.addClass('show');
			}else{
				enablePdfLabel.removeClass('show');
				enablePdfId.removeClass('show');
			}
		    if(enableSort.is(':checked')) {
		        enableSearch.addClass('show');
		        initialSort.addClass('show');
		        initialSortDirection.addClass('show');
		    }else{
		    	enableSearch.removeClass('show');
		    	initialSort.removeClass('show');
		    	initialSortDirection.removeClass('show');
		    }

		    if(enablePagination.is(':checked')) {
		        pageEntries.addClass('show');
		    }else{
		    	pageEntries.removeClass('show');
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

	enablePostLink.click(function(event) {
		enablePostLinkLabel.toggleClass('show');
	});

	enableView.click(function(event) {
		enableViewLabel.toggleClass('show');
	});

    enableEdit.click(function(event) {
		enableEditLabel.toggleClass('show');
		updateText.toggleClass('show');
		newEntryId.toggleClass('show');
	});

    enableDelete.click(function(event) {
		enableDeleteLabel.toggleClass('show');
		confirmDelete.toggleClass('show');
		deleteType.toggleClass('show')
		if(confirmDeleteCheckbox.is(':checked') && enableDelete.is(':checked')) {
	        confirmDeleteText.addClass('show');
	    }else{
			confirmDeleteText.removeClass('show');
	    }
	});

	confirmDeleteCheckbox.click(function(event) {
		confirmDeleteText.toggleClass('show')
	});

	enableDuplicate.click(function(event) {
		enableDuplicateLabel.toggleClass('show');
	});

	enablePdf.click(function(event) {
		enablePdfLabel.toggleClass('show');
		enablePdfId.toggleClass('show');
	});

	enableSort.click(function(event) {
		enableSearch.toggleClass('show');
		initialSort.toggleClass('show');
		initialSortDirection.toggleClass('show');
	});

	enablePagination.click(function(event) {
		pageEntries.toggleClass('show');
	});
});

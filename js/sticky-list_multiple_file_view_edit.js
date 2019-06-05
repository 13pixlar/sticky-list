jQuery(document).ready(function($) {

});

function validateFilesSizeAfterInitialization(uploadElementId) {
	var $ = jQuery;
	var $uploadElement = $("#"+uploadElementId);
	var settings = $($uploadElement).data('settings');
	if(settings){
		var button = typeof settings.browse_button == "string" ? $("#" + settings.browse_button) : $(settings.browse_button);

		var form_id = settings.multipart_params.form_id;
		var field_id = settings.multipart_params.field_id;
		//Count the existing files
		var totalCount = 0;
		var filesJson = $('#gform_uploaded_files_' + form_id).val();
		if(filesJson){
			filesJson = $.parseJSON(filesJson);
			var currentFieldArr = filesJson["input_"+field_id];
			if(currentFieldArr)totalCount=currentFieldArr.length;
		}

		var max = parseInt(settings.gf_vars.max_files,10);

		if( max > 0 && totalCount >= max){
			var gf_strings = typeof gform_gravityforms != 'undefined' ? gform_gravityforms.strings : {};

			var messagesID = settings.gf_vars.message_id;
			var message = gf_strings.max_reached;
			button.prop("disabled", true);
			$("#" + messagesID).prepend("<li>" + $('<div/>').text(message).html() + "</li>");
		}
	}
}

jQuery(document).on("click",".remove-entry-for-multi-file-upload",function () {
	var $ = jQuery;
	var $this = $(this);

	var gform_preview_id = $this.closest(".gform_preview_file").parent().attr("id");
	var current_file_name = $this.closest(".gform_preview_file").first().find(".gform_preview_file_link").first().attr("href");
	var gform_preview_id_arr = gform_preview_id.split("_");
	var form_id = gform_preview_id_arr[gform_preview_id_arr.length-2];
	var field_id = gform_preview_id_arr[gform_preview_id_arr.length-1];
	$this.closest(".gform_preview_file").remove();

	var $gform_uploaded_files = $('#gform_uploaded_files_'+form_id);
	var gform_uploaded_files_val = $gform_uploaded_files.val();
	if(gform_uploaded_files_val){
		var gform_uploaded_files_val_json = $.parseJSON(gform_uploaded_files_val);
		var current_field_json = gform_uploaded_files_val_json["input_"+field_id];
		var temp_field_files_arr = [];
		var old_field_files_length = current_field_json.length;
		current_field_json.forEach(function (element) {
			var existed_filename = element.existed_filename;
			if(current_file_name!=existed_filename){
				temp_field_files_arr.push(element);
			}
		});
		gform_uploaded_files_val_json["input_"+field_id] = temp_field_files_arr;
		var new_field_files_length = temp_field_files_arr.length;
		$gform_uploaded_files.val($.toJSON(gform_uploaded_files_val_json));
		if(new_field_files_length<old_field_files_length){
			var settings = $($("#gform_multifile_upload_"+form_id+"_"+field_id)).data('settings');
			var button = typeof settings.browse_button == "string" ? $("#" + settings.browse_button) : $(settings.browse_button);
			button.prop("disabled", false);
			$("#gform_multifile_messages_"+form_id+"_"+field_id).children().remove();
		}
	}
});

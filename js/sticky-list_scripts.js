jQuery(document).ready(function($) {
    if($('#enable_list').is(':checked')) {
        $('[id^=gaddon-setting-row]').css('display', 'table-row');
    }
});
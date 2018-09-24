
jQuery(document).ready(function($) {
    
    $('.response-link').on('click', function () {
    	
	  	var item = $(this).data('item'); 
	  	$('.modal-body .item-id').val(item);
	});

    $('.response-link').tooltip();
    $('#cfd_msg_modal').on('shown.bs.modal', function () {
        $('#response-body').focus();
    });

});

function cfd_checkbody() {
	var response_body = jQuery('#response-body').val();
    if (response_body != '') {
    	jQuery('#email-send').prop('disabled', false);
    } else {
    	jQuery('#email-send').prop('disabled', true);
    }
}
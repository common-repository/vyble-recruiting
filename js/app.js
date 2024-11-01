var doesvybleajax = false;

window.onload = function() {
    jQuery( window ).resize(function() {
		vresize();
	});
	
	jQuery( window ).load(function() {
		vresize();
	});
	vresize();
	
	
}

function submitvform()
{
	jQuery('.vyble_error').removeClass('error');
	jQuery('.vdscheckerror').removeClass('vdscheckerror');
	jQuery('.vformfeedback').html('');
	var error = false;
	if (jQuery('#vprename').val()=='')
	{
		error = true;
		jQuery('#vprename').addClass('vyble_error');
	}
	if (jQuery('#vsurname').val()=='')
	{
		error = true;
		jQuery('#vsurname').addClass('vyble_error');
	}
	if (jQuery('#vtel').val()=='')
	{
		error = true;
		jQuery('#vtel').addClass('vyble_error');
	}
	if (!validateEmail(jQuery('#vemail').val()))
	{
		error = true;
		jQuery('#vemail').addClass('vyble_error');
	}
	if (jQuery('#lebenslauf').val()=='')
	{
		error = true;
		jQuery('#lebenslauf').addClass('vyble_error');
	}
	if (!jQuery("#vds").is(':checked'))
	{
		error = true;
		jQuery('.vyble_vdscheck').addClass('vyble_vdscheckerror');
	}
	if (!error)
	{
		if (!doesvybleajax)
		{
			doesvybleajax = true;
			var formData = new FormData(jQuery('#vyble_vdata')[0]);
			
			jQuery.ajax({
				url: '/wp-admin/admin-ajax.php?action=vyble_send_form_action',
				type: 'POST',
				data: formData,
				success: function (data) {
					doesvybleajax = false;
					jQuery('input[type=text]').val('');
					jQuery('.vyble_vformfeedback').html('<span style="color:green">Vielen Dank für die Bewerbung!</span>');
				},
				cache: false,
				contentType: false,
				processData: false
			});
		}
	}
	else
	{
		jQuery('.vyble_vformfeedback').html('<span style="color:red">Bitte die rot markierten Eingaben prüfen!</span>');
	}
}

function vresize()
{
	var w = jQuery('.vyble_anzeigeouter').width();
	if (w<767)
	{
		jQuery('.vyble_anzeigeouter').addClass('vyble_vmobile');
	}
	else
	{
		jQuery('.vyble_anzeigeouter').removeClass('vyble_vmobile');
	}
}

function validateEmail(email) 
{ 
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}

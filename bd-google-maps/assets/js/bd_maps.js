jQuery(document).ready(function( $ ) {

	$("#pointerColor").spectrum({
	    showInput: true,
	    preferredFormat: "hex",
	    hide: function(color) {
		    $('#pinColor').val( color.toHexString() );
		}
	});
	
	$('.bd-tab').on('click',function(){
		var newTab = $(this).attr('data-id');
		if (newTab == 'bd-tab-2') {
			window.location = window.location.href + '&loc=true';
		}else{
			window.location = window.location.href + '&action=false&loc=false';
		}
	});


	$('.dealer-location').on('mouseenter',function(){
		$(this).find('.bd-actions-container').show();
	}).on('mouseleave',function(){
		$(this).find('.bd-actions-container').hide();
	});

	$('.bd-delete').on('click',function(e){
		e.preventDefault();
		var url = $(this).attr('href');
		if (confirm('Are you sure you want to delete this Location?')) {
		    window.location = url;
		}
	});


	$('.bd-add-so').on('click',function(e){
		e.preventDefault();
		$('.bd-so-container').append('<div class="so-input-wrap"><input class="bd-so-input" size="80" name="services_offered" type="text"><a class="bd-remove-so-row" href="#delete"><span class="dashicons dashicons-no"></span></a></div>');
		$('.bd-so-container .so-input-wrap input').last().focus();
	});


	$(document).on('click', '.bd-remove-so-row', function(e){
		e.preventDefault();
		$(this).parent().remove();
	});


	var the_url;
	$('.bd-data-submit').on('click',function(e){
		e.preventDefault();
		var id = $(this).attr('data-id');
		if( $(this).hasClass('edit') ){
			the_url = '../wp-content/plugins/bd-google-maps/bdEditData.php?id='+id;
		}else{
			the_url = '../wp-content/plugins/bd-google-maps/bdAddData.php';
		}
		var store_name = $('.form-table .store_name').val();
		var store_number = $('.form-table .store_number').val();
		var address = $('.form-table .address').val();
		var contact_info = $('.form-table .contact_info').val();
		var store_hours = $('.form-table .store_hours').val();
		var dealer_website = $('.form-table .dealer_website').val();
		var dealer_image = $('.form-table #bd_image_location').val();
		var email = $('.form-table .email').val();
		var additional_information = $('.form-table .additional_information').val();
		var form_services_offered = [];
		$('input.bd-so-input-check:checked').each(function() {
			var value = $(this).val()
			form_services_offered.push(value);
		});
		var services_offered = JSON.stringify(form_services_offered);
	    $.ajax({
	        type: 'POST',
	        url: the_url,
	        data: {
	        	store_name : store_name,
	        	store_number : store_number,
	        	address : address,
	        	contact_info : contact_info,
	        	store_hours : store_hours,
	        	dealer_website : dealer_website,
	        	dealer_image : dealer_image,
	        	services_offered : services_offered,
	        	email : email,
	        	additional_information : additional_information

	        }, 
	        cache: false,

	        success: function(data){
	        	if (data == 'error') {
	        		console.log('error');
	        	}else{
	        		window.location = window.location.href + '&loc=true&settings-updated';
	        	}
	        }
	    });
	});
	

	//expand location in admin
	$(document).on('click','.bd-expand',function(e){
		e.preventDefault();
		var parentContatiner = $(this).parent().parent();
		var curHeight = parentContatiner.height();
		if ( !$(this).hasClass('expand-active') ) {
			$('.dealer-location').css('height', '26px');
			$('.expand-active').removeClass('expand-active');
			autoHeight = parentContatiner.css('height', 'auto').height(); // Get Auto Height
    	    parentContatiner.height(curHeight); // Reset to Default Height
    	    parentContatiner.stop().animate({ height: autoHeight }, 500);
			$('.dealer-location').find('.bd-actions-container .bd-expand span').removeClass('dashicons-arrow-down-alt2').addClass('dashicons-arrow-up-alt2');
			$(this).find('span').removeClass('dashicons-arrow-up-alt2').addClass('dashicons-arrow-down-alt2');
			$(this).addClass('expand-active');
		}else{
			$(this).find('span').removeClass('dashicons-arrow-down-alt2').addClass('dashicons-arrow-up-alt2');
			parentContatiner.animate({ height: '26px' }, 500);
			$(this).removeClass('expand-active');
		}
	});
	

	$('.bd-so-submit').on('click',function(e){
		e.preventDefault();
		var form_services_offered = [];
		$('.bd-so-container .bd-so-input').each(function() {
			var value = $(this).val()
			form_services_offered.push(value);
		});
		var services_offered = JSON.stringify(form_services_offered);
	    $.ajax({
	        type: 'POST',
	        url: '../wp-content/plugins/bd-google-maps/bdServiceOffered.php',
	        data: {
	        	services_offered : services_offered
	        }, 
	        cache: false,
	        success: function(data){
	           var result = data;
	           result = result.trim(data);
	           console.log(result);
	           if (result == 'success') {
	           	window.scrollTo(0, 0);
	           	document.getElementById('settingsMsg').style.display = "block";
	           }else{
	           	 console.log("error")
	           }
	        }
	    });
	});


	var formfield;
 
    /* user clicks button on custom field, runs below code that opens new window */
    $('.bd-media-upload').click(function() {
        formfield = $('#bd_image_location');
        tb_show('','media-upload.php?TB_iframe=true');
        return false;
    });

    window.old_tb_remove = window.tb_remove;
    window.tb_remove = function() {
        window.old_tb_remove(); // closes media uploader widget
        formfield=null;
    };
 
    window.original_send_to_editor = window.send_to_editor;
    window.send_to_editor = function(html){
        if (formfield) {
            fileurl = $(html).attr('src');
            $(formfield).val(fileurl);
            tb_remove(); // closes media uploader widget
        } else {
            window.original_send_to_editor(html);
        }
    };





    function showLocationsMessage(){
    	$('.location-message').css('display','block');
    }

	
});
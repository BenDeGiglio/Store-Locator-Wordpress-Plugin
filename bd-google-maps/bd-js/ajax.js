jQuery(document).ready(function( $ ) {
	$('#bd-zipcode-search-form').on('submit',function(e){
		 e.preventDefault();
		 var zip = $('.bd-zipcode-search').val();
		 var so = $('#bd-services-offered option:selected').val();
		 var amt = $('.bd-zipcode-search-container').attr('data-amount');
		 var json = $('#bd-zipcode-search-form').attr('data-json');
		 var zoom = $('.bd_map').attr('data-zoom');
		 var scroll = $('.bd_map').attr('data-scroll');
		 $('.loading').show();
		 $.ajax({
		 	type: 'GET',
	        url: bd_maps_object.ajax_url,
	        data:{
	            action : 'search_locations',
	            zip : zip,
	            so : so, 
	            amt : amt, 
	            json : json,
	            zoom : zoom,
	            scroll : scroll
	        },
	        success:function(data) {
	        	var data = data;
	            data = data.trim();
	            if(data == 'Zip Error'){
	            	$('.loading').hide();
	            	alert('Please enter a zip code.');
	            }else if(data == 'No Results'){
	            	$('.loading').hide();
	            	alert('Sorry, there are no results.');
	            	console.log(data)
	            }else{
		            data = jQuery.parseJSON(data);
		          	if (json == 'false') {
		          		$('.bd_map').html('<div id="bd_show_map" style="width: 100%; height: 600px"></div>');
		          		// if you want me to do it (json must be set to false in the bd_locations shortcode for this code to work)
		          		setTimeout(function(){
		          			$('.loading').hide();
		          			$('#bd_show_map').html(data[0]);
		          			$('.bd-locations-wrap').html(data[1]);
		          		}, 2000);
		          	}else{
		          		//if you want to do your own formatting
		          		console.log(data)
		          	}
	            }
	        },
	        error: function(errorThrown){
	            console.log(errorThrown);
	        }
	    });  
	});
});
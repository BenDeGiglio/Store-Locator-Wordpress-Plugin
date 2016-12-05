<?php 
require_once('../../../wp-load.php');
global $wpdb;
$db_table = $wpdb->prefix.'bd_google_maps';
$db_link = $wpdb->prefix.'bd_google_maps_link';
$blog_id = get_current_blog_id();
$id = $_REQUEST[id];
$store_name = stripslashes(strip_tags($_POST['store_name']));
$store_number = stripslashes(strip_tags($_POST['store_number']));
$address = stripslashes(strip_tags($_POST['address']));
$address = preg_replace("/[\n\r]+/","\n",$address);

$contact_info = stripslashes(strip_tags($_POST['contact_info']));
$contact_info = preg_replace("/[\n\r]+/","\n",$contact_info);

$additional_information = stripslashes(strip_tags($_POST['additional_information']));
$additional_information = preg_replace("/[\n\r]+/","\n",$additional_information);

$email = $_POST['email'];

$services_offered = stripslashes(strip_tags($_POST['services_offered']));
$services_offered = preg_replace("/[\n\r]+/","\n",$services_offered);

$store_hours = stripslashes(strip_tags($_POST['store_hours']));
$store_hours = preg_replace("/[\n\r]+/","\n",$store_hours);

$dealer_website = $_POST['dealer_website'];

$dealer_image = $_POST['dealer_image'];

$maps_options = get_option('plugin_options', $default ); 

$geolocation_url = "https://maps.googleapis.com/maps/api/geocode/json?address=".$address."&key=".$maps_options[map_api_key];
$url_address = preg_replace("/[\s]+/","+",$geolocation_url);

$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $url_address);
$result = curl_exec($ch);
curl_close($ch);
$result = json_decode($result, true);
$lat = $result[results][0][geometry][location][lat];
$lng = $result[results][0][geometry][location][lng];

$location_data = array(
   'store_name'  =>  $store_name,
    'store_number'  =>  $store_number,
    'address'  =>  $address,
    'state'  =>  $state,
    'country'  =>  $country,
    'county'  =>  $county,
    'contact_info'  =>  $contact_info,
    'store_hours'  =>  $store_hours,
    'dealer_website'  =>  $dealer_website,
    'image'  =>  $dealer_image,
    'email'  =>  $email,
    'additional_information'  =>  $additional_information,
    'lat' => $lat,
    'lng' => $lng
);

$wpdb->update( 
	$db_table, 
	$location_data, 
	array( 'ID' => $id )
);


$wpdb->delete( $db_link, array( 'location_id' => $id, 'blog_id' => $blog_id) );

$wpdb->show_errors();


$services_offered = json_decode(stripslashes($_POST['services_offered']));


foreach ($services_offered as $so) {
    $bd_so = array(
        'location_id'  =>  $id,
        'so_id'  =>  $so,
        'blog_id' => $blog_id
    );
    $insert_data = $wpdb->insert($db_link,$bd_so);
}




if($wpdb->last_error !== ''){
    echo 'error';
}else{
    echo '';
}


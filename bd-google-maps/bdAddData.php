<?php 

require_once('../../../wp-load.php');
global $wpdb;
$db_table = $wpdb->prefix.'bd_google_maps';
$db_link = $wpdb->prefix.'bd_google_maps_link';
$blog_id = get_current_blog_id();
if ($_POST['store_name'] !== '') {
    $store_name = stripslashes(strip_tags($_POST['store_name']));
}else{
     $store_name = '';
}

if ($_POST['store_number'] !== '') {
    $store_number = stripslashes(strip_tags($_POST['store_number']));
}else{
     $store_number = '';
}

if ($_POST['address'] !== '') {
    $address = stripslashes(strip_tags($_POST['address']));
    $address = preg_replace("/[\n\r]+/","\n",$address);
}else{
     $address = '';
}

if ($_POST['contact_info'] !== '') {
    $contact_info = stripslashes(strip_tags($_POST['contact_info']));
    $contact_info = preg_replace("/[\n\r]+/","\n",$contact_info);
}else{
     $contact_info = '';
}

if ($_POST['dealer_image'] !== '') {
    $dealer_image = $_POST['dealer_image'];
}else{
     $dealer_image = '';
}

if ($_POST['email'] !== '') {
    $email = $_POST['email'];
}else{
     $email = '';
}

if ($_POST['store_hours'] !== '') {
    $store_hours = stripslashes(strip_tags($_POST['store_hours']));
    $store_hours = preg_replace("/[\n\r]+/","\n",$store_hours);
}else{
     $store_hours = '';
}

if ($_POST['dealer_website'] !== '') {
    $dealer_website = $_POST['dealer_website'];
}else{
     $dealer_website = '';
}

if ($_POST['additional_information'] !== '') {
    $additional_information = $_POST['additional_information'];
}else{
     $additional_information = '';
}


$maps_options = get_option('plugin_options', $default ); 

$geolocation_url = "https://maps.googleapis.com/maps/api/geocode/json?address=".$address."&key=".$maps_options[map_api_key];
$url_address = preg_replace("/[\s]+/","+",$geolocation_url);

$maps_options = get_option('plugin_options', $default ); 
$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $url_address);
$result = curl_exec($ch);
curl_close($ch);
$result = json_decode($result, true);
$lat = $result[results][0][geometry][location][lat];
$lng = $result[results][0][geometry][location][lng];
$county = $result[results][0][address_components][3][long_name];
$state = $result[results][0][address_components][4][long_name];
$country = $result[results][0][address_components][5][long_name];
// echo json_encode($country." : ".$state." : ".$county);


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

$insert_data = $wpdb->insert($db_table,$location_data);

$wpdb->show_errors();


$services_offered = json_decode(stripslashes($_POST['services_offered']));

$last_location = $wpdb->insert_id;

foreach ($services_offered as $so) {
    $bd_so = array(
        'location_id'  =>  $last_location,
        'so_id'  =>  $so,
        'blog_id' => $blog_id

    );
    $insert_data = $wpdb->insert($db_link,$bd_so);
}


if($wpdb->last_error !== ''){
    echo 'error';
}else{
    echo 'success';
}



 
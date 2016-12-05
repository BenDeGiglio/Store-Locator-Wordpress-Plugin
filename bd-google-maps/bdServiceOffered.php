<?php 

require_once('../../../wp-load.php');
global $wpdb;
$db_so = $wpdb->prefix.'bd_google_maps_so';

//remove current services offered
$services_offered = $wpdb->get_results("SELECT * FROM $db_so", ARRAY_A);
foreach ($services_offered as $so) {
   $soid = $so['id'];
   $wpdb->delete( $db_so, array( 'id' => $soid ) );
}

//Add new services offered
$new_so = $_REQUEST['services_offered'];
$new_so = json_decode(stripslashes($new_so));

foreach ($new_so as $newSo) {
   $so_data = array(
        'services_offered'  => $newSo
   );
   $insert_data = $wpdb->insert($db_so ,$so_data);
}


echo trim('success');









 
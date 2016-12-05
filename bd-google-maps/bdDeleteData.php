<?php 

require_once('../../../wp-load.php');
global $wpdb;
$db_table = $wpdb->prefix.'bd_google_maps';
$add_Location = $db_table;

$deleteDataID = $_REQUEST[id];

$wpdb->delete( $db_table, array( 'ID' => $deleteDataID ) );

header("Location: ".site_url()."/wp-admin/admin.php?page=bd-locations&loc=true");
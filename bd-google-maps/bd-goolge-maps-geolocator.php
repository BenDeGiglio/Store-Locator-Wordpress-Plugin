<?php
/*
Plugin Name: BD Google Maps Geolocator
Author:      Ben DeGiglio
*/ 
$siteURL = dirname(__FILE__);
$db_link = $wpdb->prefix.'bd_google_maps_link';
$db_table = $wpdb->prefix.'bd_google_maps';
$db_so = $wpdb->prefix.'bd_google_maps_so';
$blog_id = get_current_blog_id();
function bd_load_admin_files(){
    wp_enqueue_script( 'custom_js', plugins_url( '/assets/js/bd_maps.js', __FILE__ ), array('jquery') );
    wp_enqueue_style( 'custom_css',plugins_url('/assets/css/bd_maps.css', __FILE__ ) );
    wp_enqueue_script( 'color_picker_js', plugins_url( '/assets/color-picker/spectrum.js', __FILE__ ), array('jquery') );
    wp_enqueue_style( 'color_picker_css',plugins_url('/assets/color-picker/spectrum.css', __FILE__ ) );
    wp_enqueue_script('media-upload');
    wp_enqueue_script('thickbox');
    wp_enqueue_style('thickbox');
}
add_action('admin_enqueue_scripts', 'bd_load_admin_files');

function bd_load_frontend_files() {
	wp_register_script( 'bd_ajax_handle', get_template_directory_uri().'/bd-js/ajax.js', array('jquery') );
	wp_localize_script( 'bd_ajax_handle', 'bd_maps_object', array( 'ajax_url' => admin_url( 'admin-ajax.php') ) );
	wp_enqueue_script( 'bd_ajax_handle' );
    wp_enqueue_style( 'style-name',  plugins_url( '/assets/css/bd_maps.css', __FILE__ ));
    // wp_enqueue_script( 'custom_js', plugins_url( '/assets/js/bd_maps.js', __FILE__ ), array('jquery') );
    $maps_options = get_option('bd_maps_api_options', $default );
    if( isset($maps_options[users_location]) ){
		wp_enqueue_script( 'location_js', plugins_url( '/assets/js/bd_user_location.js', __FILE__ ), array('jquery') );
	}

}
add_action( 'wp_enqueue_scripts', 'bd_load_frontend_files' );
// add the admin options page
add_action('admin_menu', 'plugin_admin_add_page');
function plugin_admin_add_page() {
	add_menu_page('BD Locations Plugin Page', 'Locations', 'manage_options', 'bd-locations', 'bd_plugin_options_page', 'dashicons-location-alt');
}

function bd_plugin_options_page() { 
	global $blog_id;
	global $db_link;
	global $db_table;
	global $db_so;
	$display_options = get_option('bd_maps_display_fields', $default );	
	?>

	<div>
		<div id="settingsMsg" class="updated settings-message">
			<p><strong><?php _e('Saved Successfully!') ?></strong></p>
		</div>
		<?php if( isset($_GET['settings-updated']) ) { ?>
			<script type="text/javascript">
				window.onload = function(){
					document.getElementById('settingsMsg').style.display = "block";
				}	
			</script>
		<?php } ?>
		<h2>Google Maps Geolocator</h2>
		<ul class="bd-tabs">
			<li class="bd-tab <?php if ($_REQUEST['loc'] == 'false' || $_REQUEST['loc'] == false){echo 'active';} ?>" data-id="bd-tab-1">Settings</li>
			<li class="bd-tab <?php if ($_REQUEST['loc'] == 'true' || $_REQUEST['action'] == 'edit'){echo 'active';} ?>" data-id="bd-tab-2">Locations</li>
		</ul>
		<div class="bd-tab-container">
			<div class="bd-tab-1 bd-tab-content" <?php if ($_REQUEST['loc'] == 'false' || $_REQUEST['loc'] == false){echo 'style="display:block;"';} ?>>
				<h2>General Settings</h2>
				<form action="options.php" method="post">
				<?php settings_fields('bd_maps_plugin_options'); ?>
				<?php do_settings_sections('bd_maps_plugin'); ?>
				<?php submit_button('Save General Settings'); ?>
				</form>
				<h2>Fields Settings</h2>
				<form action="options.php" method="post">
				<?php settings_fields('bd_maps_display_settings'); ?>
				<?php do_settings_sections('bd_maps_d'); ?>
				<?php submit_button('Save Fields Settings'); ?>
				</form>
				<?php if (isset($display_options[services_offered])): ?>
				<h2>Service Offered</h2>
				<form id="bdAddSo" action="<?php echo plugin_dir_url(__FILE__).'bdServicesOffered.php'; ?>" method="post">
					<table class="form-table">
						<tr>
							<th scope="row"></th>
							<td>
								<div class="bd-so-container">
								<?php
								global $wpdb;
								$services_offered = $wpdb->get_results("SELECT * FROM $db_so", ARRAY_A);
								foreach ($services_offered as $so) {
									$so = $so['services_offered']; ?>
									<div class="so-input-wrap"><input class="bd-so-input" size="80" name="services_offered" type="text" value="<?php echo $so; ?>"><a class="bd-remove-so-row" href="#delete"><span class="dashicons dashicons-no"></span></a></div>
								<?php } ?>
								</div>
								<a class="bd-add-so" href="#add">+ Add</a>
							</td>
						</tr>
					</table>
					<p class="bd-so-submit"><input value="Save Service Offered" class="button button-primary" type="submit"></p>
				</form>
				<?php endif; ?>
				<h2>Display Settings</h2>
				<form action="options.php" method="post">
				<?php settings_fields('bd_map_settings_1'); ?>
				<?php do_settings_sections('bd_maps'); ?>
				<?php submit_button('Save Display Settings'); ?>
				</form>
			</div>
			<div class="bd-tab-2 bd-tab-content" <?php if ($_REQUEST['loc'] == 'true' || $_REQUEST['action'] == 'edit'){echo 'style="display:block;"';} ?>>
				<?php 
				if( $_REQUEST['action'] == 'edit'){ 
					if($_REQUEST['action'] === 'edit' ){
						$locationID = $_REQUEST['id'];
						$action = plugin_dir_url(__FILE__).'bdEditData.php?id='.$locationID;
						$badChars = array('<br>', '\n');
						global $wpdb;
						global $db_link;
						global $db_table;
						global $db_so;
						$editLocation = $wpdb->get_row( "SELECT * FROM $db_table WHERE id = $locationID", ARRAY_A);
						$store_name = $editLocation[store_name];
						$store_number = $editLocation[store_number];
						$contact_info = str_replace($badChars,"\n",$editLocation[contact_info]);
						$address = str_replace($badChars,"\n",$editLocation[address]);
						$services_offered = str_replace($badChars,"\n",$editLocation[services_offered]);
						$store_hours = str_replace($badChars,"\n",$editLocation[store_hours]);
						$dealer_website = $editLocation[dealer_website];
						$additional_information = str_replace($badChars,"\n",$editLocation[additional_information]);
						$email = $editLocation[email];
						$image = $editLocation[image];
						$buttonLabel = 'Update ';
					}
				}else{
					$action = plugin_dir_url(__FILE__).'bdAddData.php';
					$buttonLabel = 'Add ';
				}
				?>
				<form id="bdAddData" action="<?php echo $action; ?>" method="post">
					<table class="form-table">
						<?php if (isset($display_options[store_name])): ?>
						<tr>
							<th scope="row">Store Name</th>
							<td>
								<input size='80' class="store_name" name="store_name" type="text" value="<?php echo $store_name; ?>">
							</td>
						</tr>
						<?php endif ?>
						<?php if (isset($display_options[store_number])): ?>
						<tr>
							<th scope="row">Store Number</th>
							<td>
								<input size='80' class="store_number" name="store_number" type="text" value="<?php echo $store_number; ?>">
							</td>
						</tr>
						<?php endif ?>
						<?php if (isset($display_options[address])): ?>
						<tr>
							<th scope="row">Address</th>
							<td>
								<textarea class="address" name="address" cols="78" rows="4"><?php echo $address; ?></textarea>
							</td>
						</tr>
						<?php endif ?>
						<?php if (isset($display_options[email])): ?>
						<tr>
							<th scope="row">Email</th>
							<td>
								<input size='80' class="email" name="email" type="text" value="<?php echo $email; ?>">
							</td>
						</tr>
						<?php endif ?>
						<?php if (isset($display_options[contact_info])): ?>
						<tr>
							<th scope="row">Contact Information</th>
							<td>
								<textarea class="contact_info" name="contact_info" cols="78" rows="4"><?php echo $contact_info; ?></textarea>
							</td>
						</tr>
						<?php endif ?>
						<?php if (isset($display_options[services_offered])): ?>
						<tr>
							<th scope="row">Services Offered</th>
							<td>
								<?php
								global $wpdb;
								$current_so = [];
								$current_id = $_REQUEST['id'];
								if ($_REQUEST['action'] == 'edit') {
									$services_offered = $wpdb->get_results("SELECT * FROM $db_link WHERE location_id = $current_id AND blog_id = $blog_id", ARRAY_A);
									foreach ($services_offered as $c_so) {
										array_push($current_so, $c_so['so_id']);
									}
								}
								
								$all_services_offered = $wpdb->get_results("SELECT * FROM $db_so", ARRAY_A);
								foreach ($all_services_offered as $so) {
									$so_name = $so['services_offered']; 
									$checked = '';
									if (in_array($so['id'], $current_so)) {
										$checked = 'checked';
									}
									?>
									<div class="so-input-wrap"><input <?php echo $checked; ?> class="bd-so-input-check" size="80" name="services_offered" type="checkbox" value="<?php echo $so['id']; ?>"><label for="services_offered"><?php echo $so_name; ?></label></div>
								<?php } ?>
							</td>
						</tr>
						<?php endif ?>
						<?php if (isset($display_options[store_hours])): ?>
						<tr>
							<th scope="row">Store Hours</th>
							<td>
								<textarea class="store_hours" name="store_hours" cols="78" rows="4"><?php echo $store_hours; ?></textarea>
							</td>
						</tr>
						<?php endif ?>
						<?php if (isset($display_options[website])): ?>
						<tr>
							<th scope="row">Company Website</th>
							<td>
								<input size='80' class="dealer_website" name="dealer_website" type="text" value="<?php echo $dealer_website; ?>">
							</td>
						</tr>
						<?php endif ?>
						<?php if (isset($display_options[image])): ?>
						<tr>
							<th scope="row">Image</th>
							<td>
								<input id="bd_image_location" type="text" name="image_location" value="<?php echo $image; ?>" size="50" />
 								<input  class="bd-media-upload button" type="button" value="Upload Image" />
							</td>
						</tr>
						<?php endif ?>
						<?php if (isset($display_options[additional_information])): ?>
						<tr>
							<th scope="row">Additional Information</th>
							<td>
								<textarea class="additional_information" name="additional_information" cols="78" rows="4"><?php echo $additional_information; ?></textarea>
							</td>
						</tr>
						<?php endif ?>
					</table>
					<?php 
					$action = '';
					if ( $_REQUEST['action'] == 'edit' ) {
						$action = 'edit';
					} ?>
					<p data-id="<?php echo $locationID; ?>" class="bd-data-submit <?php echo $action; ?>"><input value="<?php echo $buttonLabel; ?> Location" class="button button-primary" type="submit"></p>
					<?php if ( $_REQUEST['action'] == 'edit' ) { ?>
					<a style="float:right;" href="../wp-admin/admin.php?page=bd-locations&action=add" class="back-to-add">Back to add new location</a>
					<?php } ?>
				</form>

				
				<?php
				global $wpdb;
				$results = $wpdb->get_results("SELECT * FROM $db_table");
				// $results = json_decode($results, true);
				?>
				<h2>Locations</h2>
				<?php 
				$i=0;
				foreach ($results as $location) {
					$bd_id = $location->id;
					$bd_store_name = $location->store_name;
					$bd_store_number = $location->store_number;
					$bd_contact_info = $location->contact_info;
					$bd_email = $location->email;
					$bd_additional_info = $location->additional_information;
					$bd_address = $location->address;
					$bd_services_offered = $location->services_offered;
					$bd_store_hours = $location->store_hours;
					$bd_dealer_website = $location->dealer_website;
					$bd_dealer_image = $location->image;
					$lat = $location->lat;
					$lng = $location->lng;
					$badChars = array('<pre>','</pre>', ',');
					$url_address = preg_replace("/[\s]+/","+",$bd_address);
					$url_address = str_replace($badChars, '', $url_address);
					$display_options = get_option('bd_maps_display_fields', $default );	
					?>
					<div id="<?php echo $bd_id; ?>" class="dealer-location">
						<?php if (isset($display_options[store_name])){ ?><h3 class="admin-store"><?php echo $bd_store_name; ?><?php if (isset($display_options[store_number])): ?>(<?php echo $bd_store_number; ?>)<?php endif ?></h3><?php } ?>
						<table class="admin-dealer-info" width="100%">
							<tr>
								<?php if (isset($display_options[image])): ?>
								<td style="width: 20%"><img style="width: 70%;height: auto;" src="<?php echo $bd_dealer_image; ?>" /></td>
								<?php endif ?>
								<td style="width: 50%">
									<?php if (isset($display_options[contact_info])): ?>
									<strong>Contact Info:</strong><pre><?php echo $bd_contact_info; ?></pre><br>
									<?php endif ?>
									<?php if (isset($display_options[email])): ?>
									<strong>Email:</strong><br><?php echo $bd_email; ?><br><br>
									<?php endif ?>
									<?php if (isset($display_options[address])): ?>
									<strong>Address:</strong><pre><?php echo $bd_address; ?></pre><br>
									<?php endif ?>
									<?php if (isset($display_options[store_hours])): ?>
									<strong>Store Hours:</strong><br><?php echo $bd_store_hours; ?><br><br>
									<?php endif ?>
									<?php if (isset($display_options[website])): ?>
									<strong>Website:</strong><br><a href="<?php echo $bd_dealer_website; ?>" target="_blank"><?php echo $bd_dealer_website; ?></a><br><br>
									<?php endif ?>
									<?php if (isset($display_options[store_hours])): ?>
									<strong>Store Hours:</strong><br><?php echo $bd_store_hours; ?><br><br>
									<?php endif ?>
									<?php if (isset($display_options[additional_information])): ?>
									<strong>Additional Information:</strong><pre><?php echo $bd_additional_info; ?><pre><br>
									<?php endif ?>
								</td>
								<?php if (isset($display_options[services_offered])): ?>
								<td>
									<p><strong>Services Offered:</strong></p>
									<?php
									$bd_so = $wpdb->get_results("SELECT * FROM $db_link WHERE location_id = $bd_id AND blog_id = $blog_id", ARRAY_A);
									foreach ($bd_so as $so) {
										$so_id = $so['so_id'];
										$blog_id = $so['blog_id'];
										$service = $wpdb->get_row( "SELECT * FROM $db_so WHERE id = $so_id" , ARRAY_A);
										if(count($bd_so) > 0){
											echo '<p>'.$service['services_offered'].'</p>';
										}
									}
									?>
								</td>
								<?php endif ?>
							</tr>
						</table>
						<div class="bd-actions-container">
							<a class="bd-expand" href="#"><span class="dashicons dashicons-arrow-up-alt2"></span></a>
							<a href="/wp-admin/admin.php?page=bd-locations&loc=true&action=edit&id=<?php echo $bd_id; ?>"><span class="dashicons dashicons-edit"></span></a>
							<a class="bd-delete" href="<?php echo plugin_dir_url(__FILE__).'bdDeleteData.php?id='.$bd_id ?>"><span class="dashicons dashicons-no"></span></a>
						</div>
						<a target="_blank" href="https://www.google.com/maps/place/<?php echo $url_address; ?>/@<?php echo $lat; ?>,<?php echo $lng; ?>">Is this the correct location?</a>
					</div>
					<?php $i++;
				} 
				?>
			</div>
		</div>
	</div>
<?php }



// ==================================
// create add options
// add the admin settings and such
// ==================================
add_action('admin_init', 'bd_maps_settings');
function bd_maps_settings(){
	register_setting( 'bd_maps_plugin_options', 'bd_maps_api_options');
	add_settings_section('bd_maps_plugin_main', '', '', 'bd_maps_plugin');
	add_settings_field(  
	    'map_api_key',                      
	    'API Key',               
	    'map_api_key_field',   
	    'bd_maps_plugin',                     
	    'bd_maps_plugin_main'
	);
	add_settings_field(  
	    'users_location',                      
	    'Get Visitors Location',               
	    'bd_user_location',   
	    'bd_maps_plugin',                     
	    'bd_maps_plugin_main'
	);
}
function map_api_key_field() {
	$options = get_option('bd_maps_api_options');
	echo "<input id='map_api_key' name='bd_maps_api_options[map_api_key]' size='80' type='text' value='{$options['map_api_key']}' /><p>Once you create create an API key you must also enable the google maps geolocation api.</p><a href='https://console.developers.google.com/flows/enableapi?apiid=maps_backend,geocoding_backend,directions_backend,distance_matrix_backend,elevation_backend,places_backend&keyType=CLIENT_SIDE&reusekey=true&pli=1' target='_blank' style='display: block; margin-top: 10px;' >Get and API Key</a> ";
} 
function bd_user_location() {
	$maps_options = get_option('bd_maps_api_options', $default );
	$checked = '';
	if( isset($maps_options[users_location]) ){
		$checked = 'checked';
	}
	echo "<input id='users_location' name='bd_maps_api_options[users_location]' type='checkbox' value='yes' ".$checked." />";
} 


add_action('admin_init', 'bd_map_settings_1');
function bd_map_settings_1(){
	register_setting( 'bd_map_settings_1', 'maps_settings_option');
	add_settings_section('bd_map', '', '', 'bd_maps');
	add_settings_field(  
	    'pin_color',                      
	    'Map Pin Color',               
	    'bd_map_pin_color',   
	    'bd_maps',                     
	    'bd_map'
	);
	add_settings_field(  
	    'map_styles',                      
	    'Map Styles <br> (Javascript style array)',               
	    'bd_map_styles',   
	    'bd_maps',                     
	    'bd_map'
	);
	add_settings_field(  
	    'search_placeholder',                      
	    'Search Box Placeholder Text',               
	    'bd_search_placeholder',   
	    'bd_maps',                     
	    'bd_map'
	);
	add_settings_field(  
	    'search_btn',                      
	    'Search Button Text',               
	    'bd_search_btn',   
	    'bd_maps',                     
	    'bd_map'
	);
}
function bd_map_pin_color(){
	$display_options = get_option('maps_settings_option', $default );
	echo '<input id="pointerColor" value="'.$display_options[pin_color].'">';
	echo '<input id="pinColor" name="maps_settings_option[pin_color]" type="hidden" value="'.$display_options[pin_color].'">';
}
function bd_map_styles(){
	$display_options = get_option('maps_settings_option', $default );
	echo '<textarea cols="80" rows="10" id="mapStyle" name="maps_settings_option[map_styles]">'.$display_options[map_styles].'</textarea>';
	echo '<a style="display: block;" href="https://snazzymaps.com" target="_blank">Styled maps here</a>';
}
function bd_search_placeholder(){
	$display_options = get_option('maps_settings_option', $default );
	echo '<input size="80" name="maps_settings_option[search_placeholder]" value="'.$display_options[search_placeholder].'">';
}
function bd_search_btn(){
	$display_options = get_option('maps_settings_option', $default );
	echo '<input size="80" name="maps_settings_option[search_btn]" value="'.$display_options[search_btn].'">';
}

add_action('admin_init', 'bd_maps_display_settings');
function bd_maps_display_settings(){
	register_setting( 'bd_maps_display_settings', 'bd_maps_display_fields');
	add_settings_section('bd_maps_display', '', '', 'bd_maps_d');
	add_settings_field(  
	    'display_fields',                      
	    'Fields to Display',               
	    'bd_maps_display_function',   
	    'bd_maps_d',                     
	    'bd_maps_display'
	);
}
function bd_maps_display_function(){
	$display_options = get_option('bd_maps_display_fields', $default );
	if( isset($maps_options[users_location]) ){
		$checked = 'checked';
	}
	echo '<input type="checkbox" name="bd_maps_display_fields[store_name]" value="yes"'. ( (isset($display_options[store_name])) ? 'checked' : '') .'>Store Name<br>
	<input type="checkbox" name="bd_maps_display_fields[store_number]" value="yes" '. ( (isset($display_options[store_number])) ? 'checked' : '') .'>Store Number<br>
	<input type="checkbox" name="bd_maps_display_fields[address]" value="yes" '. ( (isset($display_options[address])) ? 'checked' : '') .'>Address<br>
	<input type="checkbox" name="bd_maps_display_fields[email]" value="yes" '. ( (isset($display_options[email])) ? 'checked' : '') .'>Email<br>
	<input type="checkbox" name="bd_maps_display_fields[contact_info]" value="yes" '. ( (isset($display_options[contact_info])) ? 'checked' : '') .'>Contact Info<br>
	<input type="checkbox" name="bd_maps_display_fields[services_offered]" value="yes" '. ( (isset($display_options[services_offered])) ? 'checked' : '') .'>Service Offered<br>
	<input type="checkbox" name="bd_maps_display_fields[store_hours]" value="yes" '. ( (isset($display_options[store_hours])) ? 'checked' : '') .'>Store Hours<br>
	<input type="checkbox" name="bd_maps_display_fields[website]" value="yes" '. ( (isset($display_options[website])) ? 'checked' : '') .'>Website<br>
	<input type="checkbox" name="bd_maps_display_fields[image]" value="yes" '. ( (isset($display_options[image])) ? 'checked' : '') .'>Image<br>
	<input type="checkbox" name="bd_maps_display_fields[additional_information]" value="yes" '. ( (isset($display_options[additional_information])) ? 'checked' : '') .'>Additional Information<br>';	
}




// ==================================
// create SQL tables
// ==================================
function bd_google_maps_table_fn() {
	global $wpdb;

	$table_name = $wpdb->prefix . 'bd_google_maps';
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id int NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		store_name mediumtext NOT NULL,
		store_number mediumtext NOT NULL,
		contact_info longtext NOT NULL,
		address longtext NOT NULL,
		state mediumtext NOT NULL,
		country mediumtext NOT NULL,
		county mediumtext NOT NULL,
		email mediumtext NOT NULL,
		store_hours longtext NOT NULL,
		dealer_website tinytext NOT NULL,
		image longtext NOT NULL,
		additional_information longtext NOT NULL,
		lat float NOT NULL, 
		lng float NOT NULL,
		UNIQUE KEY id (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}
register_activation_hook( __FILE__, 'bd_google_maps_table_fn' );

//create table for linking services offered to a location
function bd_google_maps_link() {
	global $wpdb;

	$table_name = $wpdb->prefix . 'bd_google_maps_link';
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id int NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		location_id int,
		so_id int,
		blog_id int,
		UNIQUE KEY id (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}
register_activation_hook( __FILE__, 'bd_google_maps_link' );

//create Services Offered table
function bd_google_maps_so() {
	global $wpdb;

	$table_name = $wpdb->prefix . 'bd_google_maps_so';
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id int NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		services_offered mediumtext NOT NULL,
		UNIQUE KEY id (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}
register_activation_hook( __FILE__, 'bd_google_maps_so' );

// creates new table when a blog is created (multisite)
function on_create_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
    if ( is_plugin_active_for_network( 'bd-google-maps/bd-goolge-maps-geolocator.php' ) ) {
        switch_to_blog( $blog_id );
        bd_google_maps_so();
        bd_google_maps_link();
        bd_google_maps_table_fn();
        restore_current_blog();
    }
}
add_action( 'wpmu_new_blog', 'on_create_blog', 10, 6 );

// delete table when a blog is deleted (multisite)
function on_delete_blog( $tables ) {
    global $wpdb;
    $tables = array(
    	$wpdb->prefix . 'bd_google_maps'
    );
    return $tables;
}
add_filter( 'wpmu_drop_tables', 'on_delete_blog' );



// ==================================
// Print Map Page
// ==================================
function print_map($atts){
	global $db_link;
	global $db_table;
	global $db_so;
	$map_options = get_option('maps_settings_option', $default );
	$api_options = get_option('bd_maps_api_options', $default );
	$a = shortcode_atts( array(
        'scrollwheel' => 'true',
        'zoom' => '5'
    ), $atts );

    if ( isset($atts['scrollwheel']) ) {
    	if ($atts['scrollwheel'] === 'false') {
    		$scrollwheel = 'false';
    	}else{
    		$scrollwheel = 'true';
    	}
    }else{
    	$scrollwheel = 'true';
    }
	?>
	<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=<?php echo $api_options[map_api_key]; ?>"></script>
	<script type="text/javascript">
		jQuery(document).ready(function( $ ) { 
   			initialize();
   		});
	    <?php global $wpdb; ?>
	    function initialize() {
	    	<?php
	    	$locations = $wpdb->get_results("SELECT * FROM $db_table");
	    	$start_lat = $locations[0]->lat;
	    	$start_lng = $locations[0]->lng;
	    	?>
	        var firstLatlng = new google.maps.LatLng(<?php echo $start_lat ?>, <?php echo $start_lng ?>);              
	        var firstOptions = {
	            zoom: <?php echo $a['zoom']; ?>,
	            center: firstLatlng,
	            scrollwheel: <?php echo $scrollwheel; ?>,
	            mapTypeId: google.maps.MapTypeId.ROADMAP,
	            <?php if ($map_options[map_styles]) { ?>
	            styles: <?php echo $map_options[map_styles]; ?>
	        	<?php } ?>
	        };
	        var map = new google.maps.Map(document.getElementById("bd_show_map"), firstOptions);
	       
	    	
	        <?php 
		    
		    $i = 0;
	    	foreach ($locations as $location) {
	    		$location_id = $location->id;
	    		$display_options = get_option('bd_maps_display_fields', $default );
	    		$lat = $location->lat;
	    		$lng = $location->lng;
	    		$name = $location->store_name;
	    		$name = addslashes($name);

	    		$email = $location->email;
	    		$email = addslashes($email);

	    		$image = $location->image;

	    		$contact_info = $location->contact_info;
	    		$contact_info = preg_replace("/[\n\r]+/","<br>",$contact_info);

	    		$additional_information = $location->additional_information;
	    		$additional_information = preg_replace("/[\n\r]+/","<br>",$additional_information);

				$address = $location->address;
				$address = preg_replace("/[\n\r]+/","<br>",$address);



				$all_services_offered = $wpdb->get_results("SELECT * FROM $db_link WHERE location_id = $location_id", ARRAY_A);
				$serv = '';
				foreach ($all_services_offered as $so) {
					$so_id = $so['so_id'];
					$service = $wpdb->get_row( "SELECT * FROM $db_so WHERE id = $so_id" , ARRAY_A);
					$so_name = $service['services_offered'];
					$serv .= '<span>'.$so_name.'</span>';
				}

				$store_hours = $location->store_hours;
				$store_hours = preg_replace("/[\n\r]+/","<br>",$store_hours);

				$dealer_website = $location->dealer_website;

				$store_id = $location->store_number;

				$post_meta = $wpdb->prefix.'postmeta';

	            $bd_address = $location->address;
	            $badChars = array('<pre>','</pre>', ',');

				$url_address = preg_replace("/[\s]+/","+",$bd_address);
				$url_address = str_replace($badChars, '', $url_address);
	    	?>
	    		var coordinates_<?php echo $i; ?> = new google.maps.LatLng(<?php echo $lat; ?>, <?php echo $lng; ?>);
	    		<?php if ( isset($map_options[pin_color]) ){ ?>
	    			var pinColor = "<?php echo str_replace('#','',$map_options[pin_color]); ?>";
	    		<?php }else{ ?>
	    			var pinColor = "F76C60";
	    		<?php } ?>
		   		var pinImage = new google.maps.MarkerImage("http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|" + pinColor,
		         new google.maps.Size(21, 34),
		         new google.maps.Point(0,0),
		         new google.maps.Point(10, 34));
	    		marker_<?php echo $i; ?> = new google.maps.Marker({
		            map:map,
		            draggable:false,
		            animation: google.maps.Animation.DROP,
		            title: <?php echo "'".$name."'"; ?>,
		            position: coordinates_<?php echo $i; ?>,
		            icon: pinImage
		        });
	    		
				
		        var contentString_<?php echo $i; ?> = '<div class="bd-map-marker-info">';
		       		<?php if (isset($display_options[store_name])): ?>
		            contentString_<?php echo $i; ?> += '<h3><?php echo $name; ?></h3>';
		            <?php endif ?>
		            <?php if (isset($display_options[image])): ?>
		            contentString_<?php echo $i; ?> += '<div class="bd-left-col">';
		            contentString_<?php echo $i; ?> += '<img src="<?php echo $image; ?>" />';
		            contentString_<?php echo $i; ?> += '</div>';
		            <?php endif ?>
		            contentString_<?php echo $i; ?> += '<div class="bd-right-col">';
		            <?php if (isset($display_options[address])): ?>
		            contentString_<?php echo $i; ?> += '<p><?php echo $address; ?></p>';
		            contentString_<?php echo $i; ?> += '<p><a href="mailto:<?php echo $email; ?>"><?php echo $email; ?></a></p>';
		            <?php endif ?>
		            <?php if (isset($display_options[contact_info])): ?>
		            contentString_<?php echo $i; ?> += '<p><?php echo $contact_info; ?></p>';
		            <?php endif ?>
		            <?php if (isset($display_options[store_hours])): ?>
		            contentString_<?php echo $i; ?> += '<p><?php echo $store_hours; ?></p>';
		            <?php endif ?>
		            <?php if (isset($display_options[services_offered])): ?>
		            contentString_<?php echo $i; ?> += '<p><?php echo $serv; ?></p>';
		            <?php endif ?>
		            <?php if (isset($display_options[additional_information])): ?>
		            contentString_<?php echo $i; ?> += '<p><?php echo $additional_information; ?></p>';
		            <?php endif ?>
		            contentString_<?php echo $i; ?> += '</div>';
		            contentString_<?php echo $i; ?> += '<div class="info-links">';
		            contentString_<?php echo $i; ?> += '<a target="_blank" href="https://www.google.com/maps/place/<?php echo $url_address; ?>/@<?php echo $lat; ?>,<?php echo $lng; ?>">Directions</a>';
		            <?php if (isset($display_options[website])): ?>
		            contentString_<?php echo $i; ?> += '<a target="_blank" href="<?php echo $dealer_website ?>">Website</a>';
		            <?php endif ?>
		            contentString_<?php echo $i; ?> += '</div></div>';

	        	var infowindow_<?php echo $i; ?> = new google.maps.InfoWindow({
	            	content: contentString_<?php echo $i; ?>
	        	});
	        	google.maps.event.addListener(marker_<?php echo $i; ?>, 'click', function() {
		            infowindow_<?php echo $i; ?>.open(map,marker_<?php echo $i; ?>);
		        });
		        

	    	<?php 
	    		$i++;
	    	} 

	    	?>
	    	var int_zoom = document.getElementById('zoom-international');
	    	google.maps.event.addDomListener(int_zoom, 'click', function() {
	           map.setZoom(2);
	           map.panTo({lat: 34.208725, lng: 16.123672});
	        });
	        var dom_zoom = document.getElementById('zoom-domestic');
	        google.maps.event.addDomListener(dom_zoom, 'click', function() {
	           map.setZoom(5);
	           map.panTo({lat: 39.731923, lng: -97.042742});
	           
	        });
	    }
    </script>
	<div class="bd_map-container">
   	 <div class="bd_map" data-scroll="<?php echo $scrollwheel; ?>" data-zoom="<?php echo $a['zoom'] ?>"><div id="bd_show_map" style="width: 100%; height: 600px"></div></div>
   	</div>
<?php } 
add_shortcode( 'bd_print_map', 'print_map' );



// ==================================
// show locations function
// ==================================
function show_locations($atts){
	global $wpdb;
	global $db_link;
	global $db_table;
	global $db_so;
	global $blog_id;
	$display_options = get_option('bd_maps_display_fields', $default );
	$a = shortcode_atts( array(
        'json' => 'false',
        'user_location' => 'false',
        'amount' => '5'
    ), $atts );

    if ( isset($atts['json']) ) {
    	if ($atts['json'] === 'true') {
    		$json = 'true';
    	}else{
    		$json = 'false';
    	}
    }else{
    	$json = 'false';
    }

    if ( isset($atts['user_location']) ) {
    	if ($atts['user_location'] === 'true') {
    		$user_location = 'true';
    	}else{
    		$user_location = 'false';
    	}
    }else{
    	$user_location = 'false';
    }

    if ( isset($atts['amount']) ) {
    	$amount = $atts['amount'];
    }else{
    	$amount = $a['amount'];
    }
    if ($user_location  === 'false') {
		$locations = $wpdb->get_results("SELECT * FROM $db_table ORDER BY id DESC", ARRAY_A);
		if($json === 'true'){
			$locations =  json_encode($locations);
			return $locations;
		}else{
			?>
			<div class="bd-locations-wrap">
				<?php 
					if ( count($locations) <= $amount) {
						$num = count($locations);
					}else{
						$num = $amount;
					}
					for ($i=0; $i < $num; $i++) { 
						$store_name = $locations[$i]['store_name'];
						$location_id = $locations[$i][id];
						$contact_info = $locations[$i]['contact_info'];
						$address = $locations[$i]['address'];
						$services_offered = $locations[$i]['services_offered'];
						$store_hours = $locations[$i]['store_hours'];
						$dealer_website = $locations[$i]['dealer_website'];
						$email = $locations[$i]['email'];
						$additional_information = $locations[$i]['additional_information'];
						$image = $locations[$i]['image'];
						$distance = $locations[$i]['distance'];
						$lat = $locations[$i]['lat'];
						$lng = $locations[$i]['lng'];
			            $url_address = preg_replace("/[\s]+/","+",$address);
						$url_address = str_replace($badChars, '', $address);
						$bd_address = $locations[$i]['address'];
			            $badChars = array('<pre>','</pre>', ',');
						$url_address = preg_replace("/[\s]+/","+",$bd_address);
						$url_address = str_replace($badChars, '', $url_address);
						?>
						<div class="bd-locations-container wo-distance">
							<div>
								<?php if ($image && isset($display_options[image])): ?>
									<img src="<?php echo $image ?>" />
								<?php endif ?>
							</div>
							<div>
							<?php if ($store_name != '' && isset($display_options[store_name]) ): ?>
								<h3><?php echo $store_name; ?></h3>
							<?php endif ?>
							<?php if ($address && isset($display_options[address])): ?>
								<h4>Address</h4>
								<p><?php echo nl2br($address); ?></p>
							<?php endif ?>
							<?php if ($contact_info != '' && isset($display_options[contact_info])): ?>
								<h4>Contact</h4>
								<p><?php echo nl2br($contact_info); ?></p>
							<?php endif ?>
							<?php if ($store_hours && isset($display_options[store_hours])): ?>
								<h4>Store Hours</h4>
								<p><?php echo nl2br($store_hours); ?></p>
							<?php endif ?>
							<?php if ($email && isset($display_options[email])): ?>
								<h4>Email</h4>
								<a href="mailto:<?php echo $email; ?>"><?php echo $email; ?></a><br></p>
							<?php endif ?>
							<?php if ($additional_information && isset($display_options[additional_information])): ?>
								<h4>Additional Information</h4>
								<p><?php echo nl2br($additional_information); ?></p>
							<?php endif ?>
							</div>
							<div>
							<?php if (isset($display_options[services_offered])) :
								$bd_so = $wpdb->get_results("SELECT * FROM $db_link WHERE location_id = $location_id AND blog_id = $blog_id", ARRAY_A);
								$n = 0;								
								foreach ($bd_so as $so) {
									$so_id = $so['so_id'];
									$service = $wpdb->get_row( "SELECT * FROM $db_so WHERE id = $so_id" , ARRAY_A);
									if ($n == 0 && count($bd_so) > 0){ ?>
											<h4>Services Offered</h4>
									<?php };
									if(count($bd_so) > 0){
										echo '<p>'.$service['services_offered'].'</p>';
									}
									if ($n == count($bd_so) - 1){ ?>
									<?php };
									$n++;
								}
								$n = 0;	
							endif;?>
							<div class="bd_links">
								<a target="blank" href="https://www.google.com/maps/place/<?php echo $url_address; ?>/@<?php echo $lat; ?>,<?php echo $lng; ?>">Directions</a>
								<?php if (isset($display_options[website])): ?>
								<a target="blank" href="<?php echo $dealer_website; ?>">Website</a>
								<?php endif; ?>
							</div>
							</div>
						</div>
				<?php } ?>
			</div>
		<?php }
	}else{
    	$bd_lat =  $_COOKIE['bd_lat'];
    	$bd_lng =  $_COOKIE['bd_lng'];
		$sql = "SELECT *, ( 
		3959 * acos( cos( radians($bd_lat) ) * 
		cos( radians( lat ) ) * 
		cos( radians( lng ) - radians($bd_lng) ) + sin( radians($bd_lat) ) * 
		sin( radians( lat ) ) ) ) 
		AS distance FROM $db_table HAVING distance < 1000 ORDER BY distance";
		$locations = $wpdb->get_results($sql, ARRAY_A);
		if ( count($locations) === 0) {
			$locations = $wpdb->get_results("SELECT * FROM $db_table ORDER BY id DESC", ARRAY_A);
		}
		if($a['json'] === 'true'){
			$locations =  json_encode($locations);
			return $locations;
		}else{ ?>
			<div class="bd-locations-wrap">
				<?php 
					if ( count($locations) <= $amount) {
						$num = count($locations);
					}else{
						$num = $amount;
					}
					for ($i=0; $i < $num; $i++) {
						$store_name = $locations[$i]['store_name'];
						$location_id = $locations[$i][id];
						$contact_info = $locations[$i]['contact_info'];
						$address = $locations[$i]['address'];
						$services_offered = $locations[$i]['services_offered'];
						$store_hours = $locations[$i]['store_hours'];
						$dealer_website = $locations[$i]['dealer_website'];
						$email = $locations[$i]['email'];
						$additional_information = $locations[$i]['additional_information'];
						$image = $locations[$i]['image'];
						$distance = $locations[$i]['distance'];
						$lat = $locations[$i]['lat'];
						$lng = $locations[$i]['lng'];
			            $url_address = preg_replace("/[\s]+/","+",$address);
						$url_address = str_replace($badChars, '', $address);
						$bd_address = $locations[$i]['address'];
			            $badChars = array('<pre>','</pre>', ',');
						$url_address = preg_replace("/[\s]+/","+",$bd_address);
						$url_address = str_replace($badChars, '', $url_address);
						?>
						<div class="bd-locations-container wo-distance">
							<div>
								<?php if ($image && isset($display_options[image])): ?>
									<img src="<?php echo $image ?>" />
								<?php endif ?>
							</div>
							<div>
							<?php if ($store_name != '' && isset($display_options[store_name]) ): ?>
								<h3><?php echo $store_name; ?> <span>(<?php echo round($distance, PHP_ROUND_HALF_UP).' mi'?>)</span></h3>
							<?php endif ?>
							<?php if ($address && isset($display_options[address])): ?>
								<h4>Address</h4>
								<p><?php echo nl2br($address); ?></p>
							<?php endif ?>
							<?php if ($contact_info != '' && isset($display_options[contact_info])): ?>
								<h4>Contact</h4>
								<p><?php echo nl2br($contact_info); ?></p>
							<?php endif ?>
							<?php if ($store_hours && isset($display_options[store_hours])): ?>
								<h4>Store Hours</h4>
								<p><?php echo nl2br($store_hours); ?></p>
							<?php endif ?>
							<?php if ($email && isset($display_options[email])): ?>
								<h4>Email</h4>
								<a href="mailto:<?php echo $email; ?>"><?php echo $email; ?></a><br></p>
							<?php endif ?>
							<?php if ($additional_information && isset($display_options[additional_information])): ?>
								<h4>Additional Information</h4>
								<p><?php echo nl2br($additional_information); ?></p>
							<?php endif ?>
							</div>
							<div>
							<?php if (isset($display_options[services_offered])) :
								$bd_so = $wpdb->get_results("SELECT * FROM $db_link WHERE location_id = $location_id AND blog_id = $blog_id", ARRAY_A);
								$n = 0;								
								foreach ($bd_so as $so) {
									$so_id = $so['so_id'];
									$service = $wpdb->get_row( "SELECT * FROM $db_so WHERE id = $so_id" , ARRAY_A);
									if ($n == 0 && count($bd_so) > 0){ ?>
											<h4>Services Offered</h4>
									<?php };
									if(count($bd_so) > 0){
										echo '<p>'.$service['services_offered'].'</p>';
									}
									if ($n == count($bd_so) - 1){ ?>
									<?php };
									$n++;
								}
								$n = 0;	
							endif;?>
							<div class="bd_links">
								<a target="blank" href="https://www.google.com/maps/place/<?php echo $url_address; ?>/@<?php echo $lat; ?>,<?php echo $lng; ?>">Directions</a>
								<?php if (isset($display_options[website])): ?>
								<a target="blank" href="<?php echo $dealer_website; ?>">Website</a>
								<?php endif; ?>
							</div>
							</div>
						</div>
							
				<?php } ?>
			</div>
		<?php }
	}	
}
add_shortcode( 'bd_locations', 'show_locations' );




// ==================================
// shortcode for search
// ==================================
function display_search($atts){
	global $db_link;
	global $db_table;
	global $db_so;
	global $wpdb;

	$a = shortcode_atts( array(
        'json' => 'false',
        'amount' => '5',
        'services_offered' => 'false'
    ), $atts );


	if ( isset($atts['json']) ) {
    	if ($atts['json'] === 'true') {
    		$json = 'true';
    	}else{
    		$json = 'false';
    	}
    }else{
    	$json = 'false';
    }

	if ( isset($atts['amount']) ) {
    	$amount = $atts['amount'];
    }else{
    	$amount = $a['amount'];
    }


	if ( isset($atts['services_offered']) ) {
    	if ($atts['services_offered'] === 'true') {
    		$class = 'so';
    	}else{
    		$class = 'no-so';
    	}
    }else{
    	$class = 'no-so';
    }


    ?>

	<form id="bd-zipcode-search-form" class="<?php echo $class; ?>" data-json="<?php echo $json; ?>" action="">
		<div class="bd-zipcode-search-container" data-amount="<?php echo $amount; ?>">
			<input placeholder="Search by City or Zipcode" class="bd-zipcode-search" type="text">
		</div>
		<?php if ($class == 'so') { ?>
		<div class="bd-servicesoffered-container" >
			<select name="bd-services-offered" id="bd-services-offered">
				<option value="">Services Offered (All)</option>
			<?php 

			$services_offered = $wpdb->get_results( "SELECT * FROM $db_so" , ARRAY_A);
			foreach ($services_offered as $so) {?>
				<option value="<?php echo $so['id'] ?>"><?php echo $so['services_offered']; ?></option>
			<?php } ?>
			</select> 
		</div>
		<?php } ?>
		<div class="submit">
			<input class="bd-zicode-search-submit" value="go" type="submit">
		</div>
	</form>

<?php    
}
add_shortcode( 'bd_search', 'display_search' );


// ==================================
// create ajax action and function for zipcode search
// ==================================
add_action( 'wp_ajax_search_locations', 'bd_get_search_locations' );
add_action( 'wp_ajax_nopriv_search_locations', 'bd_get_search_locations' );
function bd_get_search_locations(){
	global $db_link;
	global $db_table;
	global $db_so;
	global $wpdb;
	$search_zip = $_REQUEST['zip'];
	$search_so = $_REQUEST['so'];
	$search_amt = $_REQUEST['amt'];
	$scroll = $_REQUEST['scroll'];
	$zoom = $_REQUEST['zoom'];
	$json = $_REQUEST['json'];
	$data = [];
	$newMap = '';
	$newData = '';
	//get lat and lng from zip
	$map_options = get_option('maps_settings_option', $default );
	$api_options = get_option('bd_maps_api_options', $default );
	$geolocation_url = "https://maps.googleapis.com/maps/api/geocode/json?address=".$search_zip."&key=".$api_options[map_api_key];
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
	if($search_so == '' && $search_zip != ''){
		$sql = "SELECT *, ( 
		3959 * acos( cos( radians($lat) ) * 
		cos( radians( lat ) ) * 
		cos( radians( lng ) - radians($lng) ) + sin( radians($lat) ) * 
		sin( radians( lat ) ) ) ) 
		AS distance FROM $db_table HAVING distance < 1000 ORDER BY distance LIMIT 0,$search_amt";
	}elseif($search_so != 'Services Offered' && $search_zip != ''){
		$sql = "SELECT l.*, ( 
		3959 * acos( cos( radians($lat) ) * 
		cos( radians( lat ) ) * 
		cos( radians( lng ) - radians($lng) ) + sin( radians($lat) ) * 
		sin( radians( lat ) ) ) ) 
		AS distance FROM $db_table l INNER JOIN $db_link so ON l.id = so.location_id WHERE so.so_id = '$search_so' HAVING distance < 1000 ORDER BY distance LIMIT 0,$search_amt";
		$locations = $wpdb->get_results($sql, ARRAY_A);
		$start_lng = $locations[0]['lng'];
		$start_lat = $locations[0]['lat'];
	}elseif($search_zip == ''){
		echo 'Zip Error';
	}

	$locations = $wpdb->get_results($sql, ARRAY_A);
	if ( count($locations) === 0) {
		'No Results';
	}else{
		$newMap .= '
		<script type="text/javascript">
			jQuery(document).ready(function( $ ) { 
	   			initialize();
	   		});';
	    global $wpdb;
	    $newMap .= 'function initialize() {}
	        var firstLatlng = new google.maps.LatLng(40.6234475, -97.2795226,5.57);              
	        var firstOptions = {
	            zoom: '.$zoom.',
	            center: firstLatlng,
	            scrollwheel: '.$scroll.',
	            mapTypeId: google.maps.MapTypeId.ROADMAP,';
	            if ($map_options[map_styles]) {
            		$newMap .= 'styles:'.$map_options[map_styles];
        		}	        
	        $newMap .= ' }; 
	        var map = new google.maps.Map(document.getElementById("bd_show_map"), firstOptions);';
		    $i = 0;

	    	foreach ($locations as $location) {
	    		$display_options = get_option('bd_maps_display_fields', $default );
	    		$map_options = get_option('maps_settings_option', $default );
	    		$location_id = $location->id;
	    		$lat = $location['lat'];
	    		$lng = $location['lng'];
	    		$name = $location['store_name'];
	    		$name = addslashes($name);
	    		$email = $location['email;'];
	    		$email = addslashes($email);
	    		$image = $location['image'];

	    		$contact_info = $location['contact_info'];
	    		$contact_info = preg_replace("/[\n\r]+/","<br>",$contact_info);

	    		$additional_information = $location['additional_information'];
	    		$additional_information = preg_replace("/[\n\r]+/","<br>",$additional_information);

				$address = $location['address'];
				$address = preg_replace("/[\n\r]+/","<br>",$address);

				$all_services_offered = $wpdb->get_results("SELECT * FROM $db_link WHERE location_id = $location_id", ARRAY_A);
				$serv = '';
				foreach ($all_services_offered as $so) {
					$so_id = $so['so_id'];
					$service = $wpdb->get_row( "SELECT * FROM $db_so WHERE id = $so_id" , ARRAY_A);
					$so_name = $service['services_offered'];
					$serv .= '<span>'.$so_name.'</span>';
				}

				$store_hours = $location['store_hours'];
				$store_hours = preg_replace("/[\n\r]+/","<br>",$store_hours);

				$dealer_website = $location['dealer_website'];

				$store_id = $location['store_number'];

				$post_meta = $wpdb->prefix.'postmeta';

	            $bd_address = $location['address'];
	            $badChars = array('<pre>','</pre>', ',');

				$url_address = preg_replace("/[\s]+/","+",$bd_address);
				$url_address = str_replace($badChars, '', $url_address);


				global $wpdb;
                $querystr = "
                    SELECT * FROM wp_postmeta WHERE meta_value = $number
                ";
                $post_link = $wpdb->get_results( $querystr, OBJECT );
                if ( ! $post_link ) {
                    $wpdb->print_error();
                }else{
                    $post_id = $post_link[0]->post_id;
                    $store_permalink = get_permalink($post_id);
                }

				$dealer_website = $store_permalink;

				$bd_address = $location['address'];
	            $badChars = array('<pre>','</pre>', ',');
				$url_address = preg_replace("/[\s]+/","+",$bd_address);
				$url_address = str_replace($badChars, '', $url_address);

	   
	    		$newMap .= 'var coordinates_'.$i.'= new google.maps.LatLng('.$lat.','.$lng.');';
	    		if ( isset($map_options[pin_color]) ){
	    			$newMap .= 'var pinColor = "'.str_replace('#','',$map_options[pin_color]).'";';
	    		}else{
	    			$newMap .= 'var pinColor = "F76C60";';
	    		}
	   			$newMap .= 'var pinImage = new google.maps.MarkerImage("http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|" + pinColor,
	            new google.maps.Size(21, 34),
	            new google.maps.Point(0,0),
	            new google.maps.Point(10, 34));		
	    		marker_'.$i.' = new google.maps.Marker({
		            map:map,
		            draggable:false,
		            animation: google.maps.Animation.DROP,
		            title: \''.$name.'\',
		            position: coordinates_'.$i.',
			        icon: pinImage
		        });
					var contentString_'. $i .'= \'<div class="bd-map-marker-info">';
	       		if ( isset($display_options[store_name]) ):
	            	$newMap .= '<h3>'. $name .'</h3>';
	            endif;
	            if (isset($display_options[image])):
	            	$newMap .= '<div class="bd-left-col">';
	            	$newMap .= '<img src="'. $image .'" />';
	            	$newMap .= '</div>';
	            endif;
	            	$newMap .= '<div class="bd-right-col">';
	            if (isset($display_options[address])):
	            	$newMap .= '<p>'. $address .'</p>';
	            	$newMap .= '<p><a href="mailto:'. $email .'">'. $email .'</a></p>';
	            endif;
	            if (isset($display_options[contact_info])):
	            	$newMap .= '<p>'. $contact_info .'</p>';
	            endif;
	            if (isset($display_options[store_hours])):
	            	$newMap .= '<p>'. $store_hours .'</p>';
	            endif;
	            if (isset($display_options[services_offered])):
	            	$newMap .= '<p>'. $serv .'</p>';
	            endif;
	            if (isset($display_options[additional_information])):
	            	$newMap .= '<p>'. $additional_information .'</p>';
	            endif;
	            $newMap .= '</div>';
	            $newMap .= '<div class="info-links">';
	            $newMap .= '<a target="_blank" href="https://www.google.com/maps/place/'. $url_address .'/@'. $lat .','. $lng .'">Directions</a>';
	            if (isset($display_options[website])):
	            	$newMap .= '<a target="_blank" href="'. $dealer_website .'">Website</a>';
	            endif;
	            $newMap .= '</div></div>\'
	        	var infowindow_'.$i.'= new google.maps.InfoWindow({
	            	content: contentString_'.$i.'
	        	});
	        	google.maps.event.addListener(marker_'.$i.', \'click\', function() {
		            infowindow_'.$i.'.open(map,marker_'.$i.');
		        });';
	    		$i++;
	    	} 
	    	$newMap .= '</script>';
	    	$data[0] = $newMap;
    	
    		//locations
    		if ($json == 'true') {
    			// keep for original plugin
    			$data[1] = $locations;
    		}else{
	    		$amount = $search_amt;
				if ( count($locations) <= $amount) {
					$num = count($locations);
				}else{
					$num = $amount;
				}
				for ($i=0; $i < $num; $i++) { 
					$store_name = $locations[$i]['store_name'];
					$location_id = $locations[$i][id];
					$contact_info = $locations[$i]['contact_info'];
					$address = $locations[$i]['address'];
					$services_offered = $locations[$i]['services_offered'];
					$store_hours = $locations[$i]['store_hours'];
					$dealer_website = $locations[$i]['dealer_website'];
					$email = $locations[$i]['email'];
					$additional_information = $locations[$i]['additional_information'];
					$image = $locations[$i]['image'];
					$distance = $locations[$i]['distance'];
					$lat = $locations[$i]['lat'];
					$lng = $locations[$i]['lng'];
					$url_address = preg_replace("/[\s]+/","+",$address);
					$url_address = str_replace($badChars, '', $address);
					$bd_address = $locations[$i]['address'];
		            $badChars = array('<pre>','</pre>', ',');
					$url_address = preg_replace("/[\s]+/","+",$bd_address);
					$url_address = str_replace($badChars, '', $url_address);
					$newData .= '<div class="bd-locations-container wo-distance"><div>';
						if ($image && isset($display_options[image])):
							$newData .= '<img src="'. $image. '" />';
						endif;
						$newData .= '</div><div>';
						if ($store_name != '' && isset($display_options[store_name]) ):
							$newData .= '<h3>'.$store_name.'<span>('.round($distance, PHP_ROUND_HALF_UP).' mi)</span></h3>';
						endif;
						if ($address && isset($display_options[address])): 
							$newData .= '<h4>Address</h4>';
							$newData .= '<p>'.nl2br($address).'</p>';
						endif; 
						if ($contact_info != '' && isset($display_options[contact_info])): 
							$newData .= '<h4>Contact</h4>';
							$newData .= '<p>'.nl2br($contact_info).'</p>';
						endif; 
						if ($store_hours && isset($display_options[store_hours])): 
							$newData .= '<h4>Store Hours</h4>';
							$newData .= '<p>'.nl2br($store_hours).'</p>';
						endif; 
						if ($email && isset($display_options[email])): 
							$newData .= '<h4>Email</h4>';
							$newData .= '<a href="mailto:'.$email.'">'.$email.'</a><br></p>';
						endif; 
						if ($additional_information && isset($display_options[additional_information])): 
							$newData .= '<h4>Additional Information</h4>';
							$newData .= '<p>'.nl2br($additional_information).'</p>';
						endif; 
						$newData .= '</div><div>';
						if (isset($display_options[services_offered])) :
							$bd_so = $wpdb->get_results("SELECT * FROM $db_link WHERE location_id = $location_id AND blog_id = $blog_id", ARRAY_A);
							$n = 0;								
							foreach ($bd_so as $so) {
								$so_id = $so['so_id'];
								$service = $wpdb->get_row( "SELECT * FROM $db_so WHERE id = $so_id" , ARRAY_A);
								if ($n == 0 && count($bd_so) > 0){ 
										$newData .= '<h4>Services Offered</h4>';
								};
								if(count($bd_so) > 0){
									$newData .= '<p>'.$service['services_offered'].'</p>';
								}
								if ($n == count($bd_so) - 1){ 
								};
								$n++;
							}
							$n = 0;	
						endif;
						$newData .= '<div class="bd_links">';
						$newData .= '<a target="blank" href="https://www.google.com/maps/place/'.$url_address.'/@'.$lat.','.$lng.'">Directions</a>';
							if (isset($display_options[website])): 
							$newData .= '<a target="blank" href="'.$dealer_website.'">Website</a>';
							endif; 
						$newData .= '</div></div></div>';
					}
				$data[1] = $newData;
			}
			echo json_encode($data);
		}
	die();
}








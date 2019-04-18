<?php
/*

These functions are used for installing and uninstalling all necessary databases, options, page, etc.. for the plugin to work properly.
*/

function mhm_memberpress_clickbank_db_activate() {
	global $wpdb;


	$table_name = $wpdb->prefix . "mhm_memberpress_clickbank_memberpress_products";
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) 
	{
		
		$sql = "CREATE TABLE " . $table_name . " (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			memberpress_id mediumint(9) NOT NULL,
			product_id mediumint(9) NOT NULL,
			price VARCHAR(30) NOT NULL,
			sku VARCHAR(255),
			created_at timestamp,

			PRIMARY KEY  (id)

		);";

		$results = $wpdb->query( $sql );

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}


}

/*USE TO REMOVE ALL THE DB 'my_plugin_db 'TABLE WHEN THE PLUGIN IS REMOVED*/
function mhm_memberpress_clickbank_db_deactivate() {
	global $wpdb;

	$table_name = $wpdb->prefix . "mhm_memberpress_clickbank_memberpress_products";
	$sql = "DROP TABLE IF EXISTS ".$table_name;
	$results = $wpdb->query( $sql );


}
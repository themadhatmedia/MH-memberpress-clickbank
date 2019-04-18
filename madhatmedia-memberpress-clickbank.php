<?php 
/**
 * Plugin Name: Madhatmedia Memberpress Clickbank Extension
 * Plugin URI: https://madhatmafia.com
 * Description:  
 * Version: 1.1
 * Author: Mad Hat Media LLC
 * Author URI: https://madhatmafia.com
 */

/* DEFINED VARIABLES */
if ( get_option( 'mhm_memberpress_clickbank_site_id' ) ) {
	define("mhm_memberpress_clickbank_site_id", get_option( 'mhm_memberpress_clickbank_site_id' ) );
}
if ( get_option( 'mhm_memberpress_clickbank_developer_key' ) ) {
	define("mhm_memberpress_clickbank_developer_key", get_option( 'mhm_memberpress_clickbank_developer_key' ) );
	
}
if ( get_option( 'mhm_memberpress_clickbank_api_key' ) ) {
	define("mhm_memberpress_clickbank_api_key", get_option( 'mhm_memberpress_clickbank_api_key' ) );
	
}

/*INCLUDE FILES*/
include ('mhm_memberpress_clickbank_get_products.php');

add_action( 'wp_enqueue_scripts', 'mhm_memberpress_clickbank_scripts' );
function mhm_memberpress_clickbank_scripts(){

  wp_register_script( 'mhm_memberpress_clickbank_js', plugins_url('js/custom.js', __FILE__), array(), false, true );
  wp_enqueue_script( 'mhm_memberpress_clickbank_js' );
  wp_localize_script( 'mhm_memberpress_clickbank_js', 'ajax_object', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

}

 if (!function_exists('mhm_plugin_verify_account')) {
	function mhm_plugin_verify_account( $email, $license_key, $product_id ) {

		$data = wp_remote_get( 'https://madhatmafia.com/woocommerce/?wc-api=software-api&request=check&email='.$email.'&license_key='.$license_key.'&product_id='.$product_id);
		if ( isset( $data["body"])) {
			$data = json_decode($data["body"]);
			return $data->success;
			
		}
	}
}

function mhm_memberpress_clickbank_admin_scripts() {
	if ( isset( $_GET['page'])) {
		if ( $_GET['page'] == 'mhm-memberpress-clickbank-setup' || $_GET['page'] == 'mhm-memberpress-clickbank-membership' || $_GET['page'] == 'mhm-memberpress-clickbank-setup-cred' ){
			// STYLE
			wp_enqueue_style('mhm_memberpress_clickbank_bootstrap',plugins_url( 'css/bootstrap.min.css', __FILE__ ),array(),date("h:i:s"));

			// SCRIPT
			wp_register_script('mhm_memberpress_clickbank_bootstrap_js', plugins_url('js/bootstrap.min.js' , __FILE__ ),array( 'jquery' ) , rand(1, 100), true);
			wp_enqueue_script('mhm_memberpress_clickbank_bootstrap_js');
  			wp_register_script( 'mhm_memberpress_clickbank_admin_js', plugins_url('js/custom-admin.js', __FILE__), array(), rand(1, 100), true );
 			wp_enqueue_script( 'mhm_memberpress_clickbank_admin_js' );
		}
	}
}
add_action('admin_head','mhm_memberpress_clickbank_admin_scripts');
$check_page_exist = get_page_by_title('Clickbank callback', 'OBJECT', 'page');
if(empty($check_page_exist)) {
    $page_id = wp_insert_post(
        array(
        'comment_status' => 'close',
        'ping_status'    => 'close',
        'post_author'    => 1,
        'post_title'     => ucwords('Clickbank callback'),
        'post_name'      => strtolower(str_replace(' ', '-', trim('Clickbank callback'))),
        'post_status'    => 'publish',
        'post_content'   => '[mhm_memberpress-clickbank-callback]',
        'post_type'      => 'page'
        )
    );
}
/* DB INITIALIZE */
include ('mhm_memberpress_clickbank_db.php');

function mhm_memberpress_clickbank_db_activate_trigger() {
	mhm_memberpress_clickbank_db_activate();


}
register_activation_hook(__FILE__, 'mhm_memberpress_clickbank_db_activate_trigger' );

function mhm_memberpress_clickbank_db_deactivate_trigger() {
	mhm_memberpress_clickbank_db_deactivate();
}
register_uninstall_hook(__FILE__, 'mhm_memberpress_clickbank_db_deactivate_trigger' );


/* DASHBOARD PAGE */
add_action('admin_menu', 'mhm_memberpress_clickbank_dashboard_menu_setup');

function mhm_memberpress_clickbank_dashboard_menu_setup() {
	add_menu_page('Memberpress-Clickbank', 'Memberpress-Clickbank', 'manage_options', 'mhm-memberpress-clickbank-setup');

	$email = get_option('mhm_memberpress_clickbank_email');
	$license_key = get_option('mhm_memberpress_clickbank_license_key');
	product_id = get_option('mhm_memberpress_clickbank_product_id');
	//$product_id = 'woocommerce-addon-retailer';
	
	add_submenu_page( 'mhm-memberpress-clickbank-setup', 'License', 'License',
	'manage_options', 'mhm-memberpress-clickbank-setup', 'mhm_memberpress_clickbank_license_setup_callback');

	$verify = mhm_plugin_verify_account( $email, $license_key, $product_id ); 
	
	if ( isset($verify ) ) {
			if ( $verify == true ) {
		
			add_submenu_page( 'mhm-memberpress-clickbank-setup', 'Setup', 'Setup',
			'manage_options', 'mhm-memberpress-clickbank-setup-cred', 'mhm_memberpress_clickbank_setup_callback');
		
			add_submenu_page( 'mhm-memberpress-clickbank-setup', 'Membership', 'Membership',
			'manage_options', 'mhm-memberpress-clickbank-membership', 'mhm_memberpress_clickbank_membership_callback'); 
			}
	}

	
}

function mhm_memberpress_clickbank_license_setup_callback() {
	if ( isset($_POST['mhm_verify_wp_membership_form']) ) {
		update_option('mhm_memberpress_clickbank_email', $_POST['email'] );
		update_option('mhm_memberpress_clickbank_license_key', $_POST['license_key'] );
		update_option('mhm_memberpress_clickbank_product_id', 'memberpress-clickbank' );
		echo "<script type='text/javascript'>
        window.location=document.location.href;
        </script>"; 
		
	}

	$email = get_option('mhm_memberpress_clickbank_email');
	$license_key = get_option('mhm_memberpress_clickbank_license_key');
	$product_id = get_option('mhm_memberpress_clickbank_product_id');
	$verify = mhm_plugin_verify_account( $email, $license_key, $product_id ); 
	
	if ( !$verify ) {
		echo "<h3>The license key entered was invalid. Please check your credentials and try again.</h3>";
	} else {
		echo "<h3>Your license is successfully verified.</h3>";
	}	
	
    ?>
    <div class="notice notice-<?php echo $data?> ">
	<h3>Software key for Madhatmedia MemberPress ClickBank plugin</h3>
		<form method="POST">
		  <p>
			<input style="display: inline;" type="text" value="<?php echo $email ?>" placeholder="Email" name="email" required />
			<input style="display: inline;" type="text" value="<?php echo $license_key ?>" placeholder="License Key" name="license_key" required />
			<input style="display: inline;" type="submit" value="Submit" name="mhm_verify_wp_membership_form" />
		  </p>
		</form>

    </div>
    <?php
}


function mhm_memberpress_clickbank_setup_callback() {
	
  if ( isset($_POST['mhm_memberpress_clickbank_setup']) ) {
  	update_option('mhm_memberpress_clickbank_site_id', $_POST['site_id']);
  	update_option('mhm_memberpress_clickbank_developer_key', $_POST['developer_key']);
		update_option('mhm_memberpress_clickbank_api_key', $_POST['api_key']);
		update_option('mhm_memberpress_clickbank_secret_key', $_POST['secret_key']);
  }
	
	echo '<div class="wrap"><div id="icon-tools" class="icon32"></div>';
	echo '<div class="row">';
?>
	  <div class="row">
		<div class="col-md-12 col-sm-6 col-xs-12">
		  <div class="panel panel-default">
			<div class="panel-heading clearfix">
			  <i class="icon-calendar"></i>
			  <h2 class="panel-title"> Memberpress Clickbank</h2>
			</div>
		   
			<div class="panel-body">
			  <form class="form-horizontal row-border" method="POST">
				<div class="form-group">
				  <label class="col-md-2 control-label">Site Id</label>
				  <div class="col-md-10">
					<input type="text" name="site_id" class="form-control" value="<?php echo (get_option( 'mhm_memberpress_clickbank_site_id' ) ) ? get_option( 'mhm_memberpress_clickbank_site_id' ) : '' ?>">
				  </div>
				</div>
				<div class="form-group">
				  <label class="col-md-2 control-label">Developer Key</label>
				  <div class="col-md-10">
					<input type="text" name="developer_key" class="form-control" value="<?php echo (get_option( 'mhm_memberpress_clickbank_developer_key' ) ) ? get_option( 'mhm_memberpress_clickbank_developer_key' ) : '' ?>">
				  </div>
				</div>
				<div class="form-group">
				  <label class="col-md-2 control-label">API Key</label>
				  <div class="col-md-10">
					<input type="text" name="api_key" class="form-control" value="<?php echo (get_option( 'mhm_memberpress_clickbank_api_key' ) ) ? get_option( 'mhm_memberpress_clickbank_api_key' ) : '' ?>">
				  </div>
				</div>
				<div class="form-group">
				  <label class="col-md-2 control-label">Secret Key</label>
				  <div class="col-md-10">
					<input type="text" name="secret_key" class="form-control" value="<?php echo (get_option( 'mhm_memberpress_clickbank_secret_key' ) ) ? get_option( 'mhm_memberpress_clickbank_secret_key' ) : '' ?>">
				  </div>
				</div>
				<div class="form-group">
				  <div class="col-md-12">
					<button type="submit" class="btn btn-info pull-right" name="mhm_memberpress_clickbank_setup">Save</button>
				  </div>
				</div>
				</div>
			  </form>
			</div>
		  </div>
		</div>
	  </div>
<?php
	echo '</div>';
	

}

function mhm_memberpress_clickbank_membership_callback() {
	global $wpdb;
	
	$clickbank_products = json_decode(mhm_memberpress_clickbank_get_products_lists(), TRUE);
	
	$args = array(
    'post_type'=> 'memberpressproduct', //TEMP
    'posts_per_page'         => '-1',
    'order'    => 'ASC'
    );              

	$get_posts = new WP_Query( $args );

	
	if ( isset($_POST['mhm_memberpress_clickbank_memberpress_add_new_record']) ) {
		if ( $_POST['mhm_memberpress_clickbank_memberpress_add_new_record'] ) {
			$exist = $wpdb->get_row( "SELECT id FROM " .$wpdb->prefix . "mhm_memberpress_clickbank_memberpress_products WHERE memberpress_id = ". $_POST['add_memberpress'] );

			if ( !$exist ) {
				$wpdb->insert( 
					$wpdb->prefix . "mhm_memberpress_clickbank_memberpress_products", 
					array( 
						'memberpress_id' => $_POST['add_memberpress'], 
						'product_id' =>  $_POST['add_click_bank'], 
						'price' =>  $_POST['add_click_bank_price'], 
						'sku' =>  $_POST['add_click_bank_sku']  
					) 
				);

				echo '
					<div class="alert alert-success">
					  <strong>Success!</strong> You had added new record.
					</div>
				';	

			} else {

				echo '
					<div class="alert alert-danger">
					  <strong>Error!</strong> You have already set the membership.
					</div>
				';	
			}
		}
	}
	elseif ( isset($_POST['mhm_memberpress_clickbank_memberpress_edit_record']) ) {
		$id = $_POST['mhm_memberpress_clickbank_memberpress_edit_record'];
		$membership_id = $_POST['edit_memberpress'];
		$product_id = $_POST['edit_click_bank'];
		$sku = $_POST['edit_click_bank_sku'];
		$price = $_POST['edit_click_bank_price'];

		$exist = $wpdb->get_row( "SELECT id FROM " .$wpdb->prefix . "mhm_memberpress_clickbank_memberpress_products WHERE memberpress_id = ". $membership_id ." AND id=". $id );
		
		//UPDATE EXISTING MEMBERSHIP
		if ($exist) {
			$wpdb->update( 
				 $wpdb->prefix . "mhm_memberpress_clickbank_memberpress_products", 
				array( 
					'product_id' 		=> $product_id,
					'price' 			=> $price,
					'sku' 				=> $sku
				), 
				array( 'id' => $id ) 
			);

			echo '
				<div class="alert alert-success">
				  <strong>Success!</strong> You had updated record.
				</div>
			';	

		} else {

			$exist = $wpdb->get_row( "SELECT id FROM " .$wpdb->prefix . "mhm_memberpress_clickbank_memberpress_products WHERE memberpress_id = ". $membership_id ." AND id <>". $id );

				if ( !$exist ) {
					$wpdb->update( 
						 $wpdb->prefix . "mhm_memberpress_clickbank_memberpress_products",
						array( 
							'product_id' 		=> $product_id,
							'memberpress_id' 	=> $membership_id,
							'price' 			=> $price,
							'sku' 				=> $sku
						), 
						array( 'id' => $id ) 
					);

					echo '
						<div class="alert alert-success">
						  <strong>Success!</strong> You had updated record.
						</div>
					';	

				} else {

					echo '
						<div class="alert alert-danger">
						  <strong>Error!</strong> You have already set the membership.
						</div>
					';	
				}
		}

	}

	elseif ( isset($_POST['mhm_memberpress_clickbank_memberpress_delete_record']) ) {
		$id = $_POST['mhm_memberpress_clickbank_memberpress_delete_record'] ;
		$wpdb->delete( $wpdb->prefix . "mhm_memberpress_clickbank_memberpress_products" , array( 'id' => $id ) );

		echo '
			<div class="alert alert-success">
			  <strong>Success!</strong> You had deleted the record.
			</div>
		';

	}
	echo '<div class="wrap"><div id="icon-tools" class="icon32"></div>';
	echo '<div class="row">';
?>
	
  <!-- Row start -->
  <div class="row">
    <div class="col-md-12">
      <div class="panel panel-default">
        <div class="panel-heading clearfix">
          <i class="icon-calendar"></i>
          <h3 class="panel-title">Add New Memberpress</h3>
        </div>
        
        <div class="panel-body">
          <form method="POST">
            <div class="form-group">
              <label for="exampleInputEmail2">Memberpress</label>
				<select class="form-control" name="add_memberpress">
					<option value="">-----</option>
				<?php 
					if ( $get_posts->posts ) {
						foreach( $get_posts->posts as $post ){
							echo "<option value='".$post->ID."' >".$post->post_title."</option>";
						}
					}
				?>
				</select>
            </div>
            <div class="form-group">
				<label for="exampleInputPassword2">Product</label>
				<select class="form-control" name="add_click_bank">
					<option value="">-----</option>
					<?php 
						foreach ($clickbank_products as $key) {
							echo "<option value='".$key['id']."' price='".$key['price']."' sku='".$key['sku']."'>".$key['title']."</option>";
						}
					?>
				</select>
				<input type="hidden" name="add_click_bank_price">
				<input type="hidden" name="add_click_bank_sku">
				
            </div>
            <button type="submit" class="btn btn-info" name="mhm_memberpress_clickbank_memberpress_add_new_record" value="1">Add</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <!-- Row end -->

  <!-- Row start -->
  <div class="row">
    <div class="col-md-12">
      <div class="panel panel-default">
        <div class="panel-heading clearfix">
          <i class="icon-calendar"></i>
          <h3 class="panel-title"></h3>
        </div>
        
        <div class="panel-body">
		 <table class="table table-hover">
			<thead>
			  <tr>
				<th>Membership</th>
				<th>Clickbank membership</th>
				<!--<th>Price</th> -->
				<th>Tools</th>
			  </tr>
			</thead>
			<tbody>
			<?php
				$clickbank_memberpress_lists = $wpdb->get_results( "SELECT * FROM " .$wpdb->prefix . "mhm_memberpress_clickbank_memberpress_products");

				foreach ($clickbank_memberpress_lists as $data ) {
				
			?>

          			<form method="POST">
					  <tr>
						<td>
							<div class="form-group">
								
								<select class="form-control" name="edit_memberpress">
									<option>-----</option>
									<?php 
										if ( $get_posts->posts ) {
											foreach( $get_posts->posts as $post ){
												$select = ($post->ID == $data->memberpress_id ) ? " selected " : "";
												echo "<option value='".$post->ID."' ".$select.">".$post->post_title."</option>";
											}
										}
									?>
								</select>
							</div>
						</td>
						<td>
							<div class="form-group">						

								<select class="form-control" name="edit_click_bank">
									<option>-----</option>
									<?php 
										foreach ($clickbank_products as $key) {
											$select = ($key['id'] == $data->product_id ) ? " selected " : "";
											echo "<option value='".$key['id']."'  sku='".$key['sku']."' price='".$key['price']."' ".$select.">".$key['title']."</option>";
										}
									?>
								</select>
								<input type="hidden" name="edit_click_bank_price" value="<?php echo  $data->price ?>">
								<input type="hidden" name="edit_click_bank_sku" value="<?php echo  $data->sku ?>">
							</div>
						</td>
						<!--
						<td>
							<div class="form-group">
								<label><?php // echo $data->price ?></label>
							</div>
						</td>
						-->
						<td>
							<div class="form-group">
								
            					<button type="submit" class="btn btn-warning" name="mhm_memberpress_clickbank_memberpress_edit_record" value="<?php echo $data->id ?>">Edit</button>
								
            					<button type="submit" class="btn btn-danger" name="mhm_memberpress_clickbank_memberpress_delete_record" value="<?php echo $data->id ?>">Delete</button>


							</div>
						</td>
					  </tr>
					</form>
				<?php } ?>
			</tbody>
		  </table>
        </div>
      </div>
    </div>
  </div>
  <!-- Row end -->

 
<?php
	echo '</div>';
}

//Clickbank DIR if exists to parent
$clickbank_dir = dirname(__FILE__)  .'/../memberpress/app/gateways/MeprClickBankGateway.php' ;

if (!file_exists($clickbank_dir)) {
		$src = dirname(__FILE__)  ."/gateway/MeprClickBankGateway.php";  

     if(!copy($src,$clickbank_dir))
     {
		echo '
			<div class="alert alert-danger">
			  <strong>Error!</strong> Unable to execute the adding of Clickbank gateway please check plugin folder permission!
			</div>
		';
     }

}

//ADD SHORTCODES
function mhm_memberpress_clickbank_callback( $atts ){
	// echo "<pre>";
	// var_dump($_GET);
	// echo "</pre>";

	if ( isset( $_GET['cbreceipt'] ) ) {
	include ( plugin_dir_path( __FILE__ ) . 'mhm_memberpress_clickbank_get_orders.php');

	
		mhm_memberpress_clickbank_get_order_lists($_GET['cbreceipt']);
	}

}
add_shortcode( 'mhm-memberpress-clickbank-callback', 'mhm_memberpress_clickbank_callback' );

add_shortcode( 'mhm-memberpress-clickbank-ipn', function() {

	include ( plugin_dir_path( __FILE__ ) . 'mhm_memberpress_clickbank_get_orders.php');
	
	$secretKey = get_option( "mhm_memberpress_clickbank_secret_key" ); // secret key from your ClickBank account
 
	// get JSON from raw body...
	$message = json_decode(file_get_contents('php://input'));
	
	// Pull out the encrypted notification and the initialization vector for
	// AES/CBC/PKCS5Padding decryption
	$encrypted = $message->{'notification'};
	$iv = $message->{'iv'};

	
	// decrypt the body...
	$decrypted = trim(
	openssl_decrypt(base64_decode($encrypted),
	'AES-256-CBC',
	substr(sha1($secretKey), 0, 32),
	OPENSSL_RAW_DATA,
	base64_decode($iv)), "\0..\32");

	
	////UTF8 Encoding, remove escape back slashes, and convert the decrypted string to a JSON object...
	$sanitizedData = utf8_encode(stripslashes($decrypted));
	$order = json_decode($decrypted);

	$download_url = $order->lineItems[0]->downloadUrl;

	$query_str = parse_url($download_url, PHP_URL_QUERY);

	parse_str($query_str, $query_params);

	$user_id = $order->vendorVariables->wp_user_id;

	$id = $order->receipt;

	mhm_memberpress_clickbank_get_order_lists( $id, $user_id );

});

add_action( 'mepr-account-subscriptions-actions', 'mhmmeprclickbank_unsubscribe', 10, 4 );

function mhmmeprclickbank_unsubscribe( $user, $subscription, $transaction, $is_sub ) {
	$subscription_id = $subscription->id;
	$option = "memberpress_receipt_subscription_$subscription_id";
	$receipt = get_option( $option );
	$expires_at = $transaction->expires_at;

	if ( $receipt !== null ) {
		?>
				<br /><a href="https://www.clkbank.com/#!/#orderLookup">Un-subscribe (Receipt: <?php echo $receipt; ?>)</a>
		<?php
	}
}

add_action( 'admin_post_mhm_memberpress_clickbank_unsubscribe', 'mhm_memberpress_clickbank_unsubscribe' );
add_action( 'admin_post_nopriv_mhm_memberpress_clickbank_unsubscribe', 'mhm_memberpress_clickbank_unsubscribe' );
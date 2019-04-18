<?php
if ( mhm_memberpress_clickbank_site_id == NULL || mhm_memberpress_clickbank_developer_key == NULL || mhm_memberpress_clickbank_api_key == NULL ) {

}

function mhm_memberpress_clickbank_get_order_lists($id=0, $wp_user_id = 0) {
	
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,"https://api.clickbank.com/rest/1.3/orders2/".$id);
    curl_setopt($ch, CURLOPT_HEADER, false); 
    curl_setopt($ch, CURLOPT_HTTPGET, true); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: application/json", "Authorization:".mhm_memberpress_clickbank_developer_key.":".mhm_memberpress_clickbank_api_key.""));
    $results = curl_exec($ch);
    curl_close($ch);
	$results = json_decode($results);
	//$output = "<option>-----</option>";
	$output= [];
	$counter = 0;
	

	if (is_array($results->orderData) ) {

			global $wpdb; 
			$data = $results->orderData[0]->lineItemData;
			$user_ID = $wp_user_id == 0 ? get_current_user_id() : $wp_user_id;
			$clickbank_memberpress_product = $wpdb->get_row( "SELECT * FROM " .$wpdb->prefix . "mhm_memberpress_clickbank_memberpress_products WHERE sku=".$data->itemNo);
	  
			$active_str= 'active';
			$complete_str='complete';
			$payment_str = 'clickbank';


			$get_subscription = $wpdb->get_row( "SELECT * FROM " .$wpdb->prefix . "mepr_subscriptions WHERE user_id=".$user_ID." AND product_id =".$clickbank_memberpress_product->memberpress_id);



		 
			$gateway = ( $get_subscription->gateway ) ? $get_subscription->gateway : 'clickbank';

			$check_subs = $wpdb->get_row( "SELECT * FROM " .$wpdb->prefix . "mepr_subscriptions WHERE user_id=".$user_ID." AND status = 'active' AND product_id =".$clickbank_memberpress_product->memberpress_id);


			if (!$check_subs) {

			  if ( $get_subscription->subscr_id ) {
				$sub = new MeprSubscription(); 
				$sub->user_id = $user_ID;
				$sub->product_id = $clickbank_memberpress_product->memberpress_id;
				$sub->price = $data->rebillAmount;
				$sub->total = $data->rebillAmount;
				$sub->period = 1; //TEMP
				$sub->period_type = 'months';
				$sub->status = MeprSubscription::$active_str;
				$sub_id = $sub->store();
			  } else {
				$sub_id = $get_subscription->subscr_id;
			  }




			  $expires_at = ( $data->nextPaymentDate->{'@nil'} ) ? gmdate('Y-m-d 23:59:59', (time() + MeprUtils::months(1))) : $data->nextPaymentDate;


			  $txn = new MeprTransaction();
			  $txn->amount = $data->rebillAmount;
			  $txn->total = $data->rebillAmount;
			  $txn->user_id = $user_ID;
			  $txn->product_id = $clickbank_memberpress_product->memberpress_id;
			  $txn->status = MeprTransaction::$complete_str;
			  $txn->txn_type = MeprTransaction::$payment_str;
			  $txn->gateway = $gateway; 
			  $txn->expires_at = $expires_at;
			  $txn->subscription_id = $sub_id;
			  $txn->store();
		  }
		

		
	} else {
		if ( isset($results->orderData->lineItemData) ) {
			global $wpdb; 
			$data = $results->orderData->lineItemData;
			$user_ID = $wp_user_id == 0 ? get_current_user_id() : $wp_user_id;
			$clickbank_memberpress_product = $wpdb->get_row( "SELECT * FROM " .$wpdb->prefix . "mhm_memberpress_clickbank_memberpress_products WHERE sku=".$data->itemNo);
	  
			$active_str= 'active';
			$complete_str='complete';
			$payment_str = 'clickbank';


			$get_subscription = $wpdb->get_row( "SELECT * FROM " .$wpdb->prefix . "mepr_subscriptions WHERE user_id=".$user_ID." AND product_id =".$clickbank_memberpress_product->memberpress_id);


		 
			$gateway = ( $get_subscription->gateway ) ? $get_subscription->gateway : 'clickbank';

			$check_subs = $wpdb->get_row( "SELECT * FROM " .$wpdb->prefix . "mepr_subscriptions WHERE user_id=".$user_ID." AND status = 'active' AND product_id =".$clickbank_memberpress_product->memberpress_id);


			if (!$check_subs) {

			  if ( $get_subscription->subscr_id ) {
				$sub = new MeprSubscription(); 
				$sub->user_id = $user_ID;
				$sub->product_id = $clickbank_memberpress_product->memberpress_id;
				$sub->price = $data->rebillAmount;
				$sub->total = $data->rebillAmount;
				$sub->period = 1; //TEMP
				$sub->period_type = 'months';
				$sub->status = MeprSubscription::$active_str;
				$sub_id = $sub->store();
			  } else {
				$sub_id = $get_subscription->subscr_id;
			  }




			  $expires_at = ( $data->nextPaymentDate->{'@nil'} ) ? gmdate('Y-m-d 23:59:59', (time() + MeprUtils::months(1))) : $data->nextPaymentDate;


			  $txn = new MeprTransaction();
			  $txn->amount = $data->rebillAmount;
			  $txn->total = $data->rebillAmount;
			  $txn->user_id = $user_ID;
			  $txn->product_id = $clickbank_memberpress_product->memberpress_id;
			  $txn->status = MeprTransaction::$complete_str;
			  $txn->txn_type = MeprTransaction::$payment_str;
			  $txn->gateway = $gateway; 
			  $txn->expires_at = $expires_at;
			  $txn->subscription_id = $sub_id;
			  $txn->store();
		  }
		}
	}

  $option = "memberpress_receipt_subscription_$sub_id";

  if ( get_option( $option ) == null ) {
    add_option( $option, $id );
  } else {
    update_option( $option, $id );
  }

}


/*
https://bethub.pro/clickbank-callback/?item=3&cbreceipt=95WJABGG&time=1550531498&cbpop=55C722B8&cbaffi=0&cname=Jayvee+Testing&cemail=test%40mailinator.com&ccountry=PH&czip=8000&cbitems=3

array(10) {
  ["item"]=>
  string(1) "3"
  ["cbreceipt"]=>
  string(8) "95WJABGG"
  ["time"]=>
  string(10) "1550531498"
  ["cbpop"]=>
  string(8) "55C722B8"
  ["cbaffi"]=>
  string(1) "0"
  ["cname"]=>
  string(14) "Jayvee Testing"
  ["cemail"]=>
  string(19) "test@mailinator.com"
  ["ccountry"]=>
  string(2) "PH"
  ["czip"]=>
  string(4) "8000"
  ["cbitems"]=>
  string(1) "3"
}
object(stdClass)#5985 (1) {
  ["orderData"]=>
  object(stdClass)#6002 (23) {
    ["transactionTime"]=>
    string(25) "2019-02-18T15:11:38-08:00"
    ["receipt"]=>
    string(8) "95WJABGG"
    ["trackingId"]=>
    object(stdClass)#5990 (1) {
      ["@nil"]=>
      string(4) "true"
    }
    ["paytmentMethod"]=>
    string(4) "TEST"
    ["transactionType"]=>
    string(9) "TEST_SALE"
    ["totalOrderAmount"]=>
    string(5) "11.94"
    ["totalShippingAmount"]=>
    string(3) "0.0"
    ["totalTaxAmount"]=>
    string(4) "0.00"
    ["vendor"]=>
    string(6) "BETHUB"
    ["affiliate"]=>
    object(stdClass)#5992 (1) {
      ["@nil"]=>
      string(4) "true"
    }
    ["country"]=>
    string(2) "PH"
    ["state"]=>
    object(stdClass)#5233 (1) {
      ["@nil"]=>
      string(4) "true"
    }
    ["lastName"]=>
    string(7) "TESTING"
    ["firstName"]=>
    string(6) "JAYVEE"
    ["currency"]=>
    string(3) "PHP"
    ["declinedConsent"]=>
    object(stdClass)#5237 (1) {
      ["@nil"]=>
      string(4) "true"
    }
    ["email"]=>
    string(19) "test@mailinator.com"
    ["postalCode"]=>
    string(4) "8000"
    ["role"]=>
    string(6) "VENDOR"
    ["fullName"]=>
    string(14) "Jayvee Testing"
    ["customerRefundableState"]=>
    object(stdClass)#5268 (1) {
      ["@nil"]=>
      string(4) "true"
    }
    ["vendorVariables"]=>
    object(stdClass)#5987 (1) {
      ["item"]=>
      object(stdClass)#5238 (2) {
        ["name"]=>
        string(7) "cbitems"
        ["value"]=>
        string(1) "3"
      }
    }
    ["lineItemData"]=>
    object(stdClass)#5986 (14) {
      ["itemNo"]=>
      string(1) "3"
      ["productTitle"]=>
      string(19) "gateSpeed-ClickBank"
      ["recurring"]=>
      string(4) "true"
      ["shippable"]=>
      string(5) "false"
      ["customerAmount"]=>
      string(5) "11.94"
      ["accountAmount"]=>
      string(4) "9.06"
      ["quantity"]=>
      string(1) "1"
      ["lineItemType"]=>
      string(8) "STANDARD"
      ["rebillAmount"]=>
      string(5) "10.00"
      ["processedPayments"]=>
      string(1) "1"
      ["futurePayments"]=>
      string(3) "998"
      ["nextPaymentDate"]=>
      string(25) "2019-03-18T15:11:38-07:00"
      ["status"]=>
      string(6) "ACTIVE"
      ["role"]=>
      string(6) "VENDOR"
    }
  }
}
*/


    ?>
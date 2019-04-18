<?php
if ( mhm_memberpress_clickbank_site_id == NULL || mhm_memberpress_clickbank_developer_key == NULL || mhm_memberpress_clickbank_api_key == NULL ) {

}

function mhm_memberpress_clickbank_get_products_lists() {
	
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,"https://api.clickbank.com/rest/1.3/products/list?site=".mhm_memberpress_clickbank_site_id);
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


	if ( $results->products->product ) :
		foreach ($results->products->product as $key) {
			//echo 'SKU: '.$key->{"@sku"}. ' id: '.$key->id. ' title: '.$key->title. ' title: '.$key->pricings->pricing->standard->price->native_price. "<br>";
			//$output .= "<option value='".$key->id."' price='".$key->pricings->pricing->standard->price->native_price."'>".$key->title."</option>";
			$output[$counter]["id"] = $key->id;
			$output[$counter]["price"] = $key->pricings->pricing->standard->price->native_price;
			$output[$counter]["title"] = $key->title;
            $output[$counter]["sku"] = $key->{"@sku"};
			$counter++;
			
		}
	endif;

	return json_encode($output);
}
	

/*

object(stdClass)#2 (1) {
  ["product"]=&gt;
  object(stdClass)#3 (27) {
    ["@sku"]=&gt;
    string(1) "1"
    ["id"]=&gt;
    string(7) "1329845"
    ["status"]=&gt;
    string(6) "ACTIVE"
    ["digital"]=&gt;
    string(4) "true"
    ["physical"]=&gt;
    string(5) "false"
    ["digitalRecurring"]=&gt;
    string(5) "false"
    ["physicalRecurring"]=&gt;
    string(5) "false"
    ["site"]=&gt;
    string(6) "bethub"
    ["created"]=&gt;
    string(23) "2019-01-31 01:35:28 PST"
    ["updated"]=&gt;
    string(23) "2019-01-31 01:35:27 PST"
    ["approval_status"]=&gt;
    object(stdClass)#4 (1) {
      ["status"]=&gt;
      string(17) "APPROVAL_REQUIRED"
    }
    ["language"]=&gt;
    string(2) "EN"
    ["title"]=&gt;
    string(16) "Dev Test Product"
    ["thank_you_pages"]=&gt;
    object(stdClass)#5 (1) {
      ["desktop"]=&gt;
      string(27) "https://bethub.pro/pitch-ty"
    }
    ["pitch_pages"]=&gt;
    object(stdClass)#6 (1) {
      ["desktop"]=&gt;
      string(29) "https://bethub.pro/pitch-page"
    }
    ["pricings"]=&gt;
    object(stdClass)#7 (1) {
      ["pricing"]=&gt;
      object(stdClass)#8 (2) {
        ["@currency"]=&gt;
        string(3) "USD"
        ["standard"]=&gt;
        object(stdClass)#9 (1) {
          ["price"]=&gt;
          object(stdClass)#10 (3) {
            ["native_price"]=&gt;
            string(4) "3.00"
            ["usd"]=&gt;
            string(4) "3.00"
            ["usd_with_fees"]=&gt;
            string(4) "3.00"
          }
        }
      }
    }
    ["categories"]=&gt;
    object(stdClass)#11 (1) {
      ["category"]=&gt;
      string(11) "MEMBER_SITE"
    }
    ["no_commission"]=&gt;
    string(5) "false"
    ["sale_refund_days_limit"]=&gt;
    string(2) "60"
    ["rebill_refund_days_limit"]=&gt;
    string(2) "60"
    ["commission_tier_override"]=&gt;
    string(4) "true"
    ["isPartOfOrderBump"]=&gt;
    string(5) "false"
    ["isInitialOfOrderBump"]=&gt;
    string(5) "false"
    ["isProductOfOrderBump"]=&gt;
    string(5) "false"
    ["phoneNumberOnOrderForm"]=&gt;
    string(5) "false"
    ["delayedDelivery"]=&gt;
    string(5) "false"
    ["sendRebillNotification"]=&gt;
    string(4) "true"
  }
}

*/


    ?>
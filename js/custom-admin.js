jQuery(document).ready( function($){
    $("select[name='add_click_bank']").change(function() {
    	var price = $(this).find(":selected").attr("price");
    	var sku = $(this).find(":selected").attr("sku");
    	console.log("PRICE "+ price);
    	console.log("SKU "+ sku);
    	$(this).parent().find("[name='add_click_bank_price']").val(price);
    	$(this).parent().find("[name='add_click_bank_sku']").val(sku);
    });

 	$("select[name='edit_click_bank']").change(function() {
    	var price = $(this).find(":selected").attr("price");
    	var sku = $(this).find(":selected").attr("sku");
    	 console.log("PRICE "+ price);
    	 console.log("SKU "+ sku);
    	 $(this).parent().find("[name='edit_click_bank_price']").val(price);
    	 $(this).parent().find("[name='edit_click_bank_sku']").val(sku);
    	 var output = '<div class="form-group"><label>'+price+'</label></div>';
    	 //$(this).parent().parent().parent().find("td:eq(2)").html(output);
    });
      
});
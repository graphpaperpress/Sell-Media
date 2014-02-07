jQuery(document).ready(function($){

	$('#sell_media_item_size').change(function(){
		var item_price = $(this).children(':selected').data('price');
		alert(item_price);
	});

});
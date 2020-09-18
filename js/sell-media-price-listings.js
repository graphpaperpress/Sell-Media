(function( $ ) {
  "use strict";
	var sell_media_price_listing = {
		options : {
      blank_data : {},
      after_delete : function( e ){

      }
    },
		init : function( options ){
			var $this = this;
			this.options = $.extend( {}, this.options, options );

			this.fetch_data()
			this.add_new()
			this.delete_data();
		},
		fetch_data : function() {
			var $this = this;
			var obj = $this.options.data;
			if( obj.length > 0 ){
				$this.append( obj );
			}
      else{
        this.add_new_row();
      }
		},
		add_new : function() {
			var $this = this;
			$( document ).on( 'click', this.options.add_button, function(){
				$this.add_new_row();
			});
		},
    add_new_row : function() {
      var index = $( this.options.wrapper_element ).children().last().data('index');
      var data = {
        index : (typeof index == 'undefined' ) ? 0 : index + 1,
      }
      data = [$.extend({},data,this.options.blank_data)];
      this.append( data );
    },
		delete_data : function() {
			var $this = this;
			$( document ).on( 'click', this.options.delete_button, function(){
				if( ! confirm( $(this).data( 'message' ) ) )
					return false;
				var index = $(this).data( 'index' );
				$( $this.options.wrapper_element ).find( '#_row-data-' + index ).remove();
        $this.options.after_delete( $(this) );
        return false;
			} );
		},
		append: function( input ) {
			var $this = this;
			var post_template = wp.template( $this.options.wp_template );
			$( $this.options.wrapper_element ).append( post_template( input ) );
		},
	}

  var defualt_listing_args = {
		add_button : '#sell-media-add-button',
		delete_button: '.sell-media-price-group-delete-term',
		wp_template : 'sm-download-group-post',
    after_delete : function( e ){
      var old_value = $('[name=deleted_term_ids]').val();
      var new_value = ( '' !== old_value ) ? old_value +',' + e.data( 'termid' ) : e.data( 'termid' );
      $('[name=deleted_term_ids]').val( new_value )
    }
	}
  if( 'undefined' !== typeof( sell_media_price_listings['price-group'] ) ){
    var defualt_listing_args = $.extend( {}, defualt_listing_args, {
      wrapper_element : 'table#sell-media-price-table.tax-price-group tbody',
      data: sell_media_price_listings['price-group'],
    } );
    sell_media_price_listing.init( defualt_listing_args );
  }

  if( 'undefined' !== typeof( sell_media_price_listings['reprints-price-group'] ) ){
    var defualt_listing_args = $.extend( {}, defualt_listing_args, {
      wrapper_element : 'table#sell-media-price-table.tax-reprints-price-group tbody',
      data: sell_media_price_listings['reprints-price-group'],
    } );
    sell_media_price_listing.init( defualt_listing_args );
  }

  $( '.tab-price-lists select' ).change( function(){
    var link = $(this).val();
    window.location = link;
  });

  $( '.tab-title a' ).click( function(){
    $(this).next().toggle();
  });

  $('#sell-media-pricelist-form, #sell-media-new-pricelist-form').parsley();

  // Delete Pricelist.
  $(document).on( 'click', '.tab-price-lists .deletion', function(e){
   var href = $(this).data('href');
   var message = $(this).data('message');
   if(!confirm( message ) ){
    return false;
   }
   window.location = href;
   return false;
  });
})(jQuery);

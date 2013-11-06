;(function( $ ){

	$( document ).ready(function( $ ){

		// iris color picker from core wp
		$( '.color-picker' ).wpColorPicker();

		// font previews when user chooses use this font
		$( '#gpp-font-preview .box button' ).live( 'click', function() {
			$( this ).each( function() {

				var header = $( this ).attr( 'data-font-header' );
				if ( typeof( header ) !== 'undefined' ) {
					$( "select[name*='[font]'] option[value='"+header+"']:first" ).attr( "selected", true );
				}

				var body = $( this ).attr( 'data-font-body' );
				if ( typeof( body ) !== 'undefined' ){
					$( "select[name*='[font_alt]'] option[value='"+body+"']:first" ).attr( "selected", true );
				}

				tb_remove();
			});
		});
	});

})(jQuery);
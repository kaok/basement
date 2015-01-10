

// WordPress color picker
if ( $( '.basement_color_picker' ).length ) {
	$.each( $( '.basement_color_picker' ), function( index, colorpicker ) {
		$colorpicker = $( colorpicker );

		$colorpicker.on( 'blur focus change', function() {
			$(this).trigger( 'basement_colorpicker_changed' );
		});

		setColorPickerReceivers( $colorpicker );
	});
}

// Update color picker receivers data (values, text, backgrounds, etc.)
function setColorPickerReceivers( $colorpicker, color ) {
	// Update background color receivers
	var backgroundColorReceivers = $colorpicker.data( 'background-color-receivers' );
	if ( backgroundColorReceivers && $( backgroundColorReceivers ).length ) {
		$( backgroundColorReceivers ).css( 'backgroundColor', '#' + color );
	}
}
// Handle senders triggers click
if ( ( valueSendersClickers = $( '.basement_update_receivers[data-action="click"]' ) ).length ) {
	valueSendersClickers.click( function( event ) {
		event.preventDefault();
		if ( $( this ).data( 'sender-value' ) ) {
			value = $( this ).data( 'sender-value' );
		} else {
			value = '';
		}
		updateValueReceivers( $( this ), value );
	});
}

// Handle senders triggers change
if ( ( valueSendersChangers = $( '.basement_update_receivers[data-action="change"]' ) ).length ) {
	valueSendersChangers.change( function( event ) {
		updateValueReceivers( $( this ) );
	});
}

// Handle colorpicker triggers
if ( ( valueSendersChangers = $( '.basement_color_picker.basement_update_receivers' ) ).length ) {
	valueSendersChangers.on( 'keyup change blur', function( event ) {
		updateValueReceivers( $( this ) );
	});
}

// Updates all according nodes and attributes
function updateValueReceivers( $sender, value ) { 
	var textReceivers = $sender.data( 'text-receivers' ),
		valueReceivers = $sender.data( 'value-receivers' ),
		backgroundColorReceivers = $sender.data( 'background-color-receivers' ),
		backgroundImageReceivers = $sender.data( 'background-image-receivers' ),
		backgroundPositionReceivers = $sender.data( 'background-position-receivers' ),
		backgroundXPositionReceivers = $sender.data( 'background-x-position-receivers' ),
		backgroundRepeatReceivers = $sender.data( 'background-repeat-receivers' ),
		backgroundAttachmentReceivers = $sender.data( 'background-attachment-receivers' ),
		backgroundSizeReceivers = $sender.data( 'background-size-receivers' );

	if ( !value ) {
		value = $sender.val();
	}

	updateSrcReceivers( $sender, value );
	updateValReceivers( $sender, value );

	// Update value of text nodes
	if ( textReceivers && ( textReceivers = $( textReceivers ) ).length ) {
		textReceivers.text( value );
	}

	// Update value of backgrounds receivers
	if ( backgroundImageReceivers && ( backgroundImageReceivers = $( backgroundImageReceivers ) ).length ) {
		if ( value ) {
			backgroundImageReceivers.css( 'backgroundImage', 'url(' + value + ')' );
		} else {
			backgroundImageReceivers.css( 'backgroundImage', 'none' );
		}
	}

	// Update value of background color receivers
	if ( backgroundColorReceivers && ( backgroundColorReceivers = $( backgroundColorReceivers ) ).length ) {
		if ( value ) {
			backgroundColorReceivers.css( 'backgroundColor', value );
		} else {
			backgroundColorReceivers.css( 'backgroundColor', 'transparent' );
		}
	}

	// Update value of backgrounds x position
	if ( backgroundPositionReceivers && 
		( backgroundPositionReceivers = $( backgroundPositionReceivers ) ).length ) {
		$.each( backgroundPositionReceivers, function( index, receiver ) {
			$receiver = $( receiver );
			if ( !value ) {
				value = 'left top';
			}
			$receiver.css( 'backgroundPosition',  value );
		});
	}

	// Update value of backgrounds x position
	if ( backgroundXPositionReceivers && 
		( backgroundXPositionReceivers = $( backgroundXPositionReceivers ) ).length ) {
		$.each( backgroundXPositionReceivers, function( index, receiver ) {
			backgroundYPosition = getBackgroundCSSPosition(receiver ).y;
			$receiver = $( receiver );
			if ( !value ) {
				value = 'left';
			}
			$receiver.css( 'backgroundPosition',  value + ' ' + backgroundYPosition );
		});
	}

	// Update backgrounds repeat value
	if ( backgroundRepeatReceivers && 
		( backgroundRepeatReceivers = $( backgroundRepeatReceivers ) ).length ) {
		$.each( backgroundRepeatReceivers, function( index, receiver ) {
			$receiver = $( receiver );
			if ( !value ) {
				value = 'no-repeat';
			}
			$receiver.css( 'backgroundRepeat',  value );
		});
	}

	// Update backgrounds attachment value
	if ( backgroundAttachmentReceivers && 
		( backgroundAttachmentReceivers = $( backgroundAttachmentReceivers ) ).length ) {
		if ( !value ) {
			value = 'scroll';
		}
		backgroundAttachmentReceivers.css( 'backgroundAttachment',  value );
	}

	// Update backgrounds size value
	if ( backgroundSizeReceivers && 
		( backgroundSizeReceivers = $( backgroundSizeReceivers ) ).length ) {
		if ( !value ) {
			value = 'auto';
		}
		backgroundSizeReceivers.css( 'backgroundSize',  value );
	}
	
}

function updateSrcReceivers( $sender, value ) { 
	var srcReceivers = $sender.data( 'src-receivers' );
	// Update value of images srcs
	if ( srcReceivers && ( srcReceivers = $( srcReceivers ) ).length ) {
		srcReceivers.attr('src', value);
	}
}

function updateValReceivers( $sender, value ) { 
	var valueReceivers = $sender.data( 'value-receivers' );
	// Update value of inputs
	if ( valueReceivers && ( valueReceivers = $( valueReceivers ) ).length ) {
		valueReceivers.val( value ).trigger( 'change' );
	}
}

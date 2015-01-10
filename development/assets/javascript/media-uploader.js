// WordPress 3.5+ media uploader for Basement buttons
if ( $( '.basement_media_upload' ).length ) {
	$( '.basement_media_upload' ).click( function( event ) {
		var $that = jQuery(this),
			libraryType = $(this).data( 'library-type' ),
			frameTitle = $(this).data( 'frame-title' ),
			frameButtonText = $(this).data( 'button-text' );

		if (!libraryType) {
			libraryType = 'image';
		}

		if (!frameTitle) {
			frameTitle = 'Choose';
		}

		if (!frameButtonText) {
			frameButtonText = 'Update';
		}

		event.preventDefault();

		// if its not null, its broking media_uploader_frame's onselect "activeFileUploadContext"
		media_uploader_frame = null;

		// Create the media frame.
		media_uploader_frame = wp.media.frames.customHeader = wp.media( {
			// Set the title of the modal.
			title: frameTitle,

			// Tell the modal to show media type is needed. Ignore if want ALL
			library: {
				type: libraryType
			},
			// Customize the submit button.
			button: {
				// Set the text of the button.
				text: frameButtonText
			}
		});

		media_uploader_frame.on( "select", function() {
			updateValueReceivers( $that, media_uploader_frame.state().get("selection").first().attributes.url );
			updateValReceivers( $that, media_uploader_frame.state().get("selection").first().attributes.id );
			$that.parents( '.basement_form_media_uploader_wrapper' ).addClass( 'file_loaded' );
		});

		media_uploader_frame.open();
		return false;
	});
}

if ( $( '.basement_media_delete' ).length ) {
	$( '.basement_media_delete' ).click( function( event ) {
		updateValueReceivers( $( this ), '' );
		$(this).parents( '.basement_form_media_uploader_wrapper' ).removeClass( 'file_loaded' );
	} );
}

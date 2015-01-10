// Handle updaters triggers click
if ( ( valueSendersClickers = $( '.basement_update_nodes[data-action="click"]' ) ).length ) {
	valueSendersClickers.click( function( event ) {
		event.preventDefault();
		updateNodes( $( this ) );
	});
}

// Updates all according nodes
function updateNodes( $sender ) { 
	var removeCommandReceivers = $sender.data( 'remove-node' );

	// Update value of text nodes
	if ( removeCommandReceivers && ( removeCommandReceivers = $( removeCommandReceivers ) ).length ) {
		removeCommandReceivers.remove();
	}

}
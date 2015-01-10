(function() {
	var settings_pages = $('.basement_settings_page');
	if ( settings_pages.length ) {
		$.each(settings_pages, function(index, setting_page) {
			(function(setting_page) {
				var menu = setting_page.find('.basement_settings_panel_menu'),
					setting_pageTopOffset = setting_page.offset().top,
					menu_items = menu.find('a'),
					sections = setting_page.find('.basement_settings_panel_sections > div');

				// Show active settings section
				var show_active_section = function( menu_item ) {
					if ( !menu_item ) {
						var hash = window.location.hash,
							possible_item;
						if ( hash.length ) {
							possible_item = menu_items.filter( '[data-section="' + hash.slice(1) + '"]' );
							if ( possible_item.length ) {
								menu_item = possible_item;
							}
						}
						if ( !possible_item ) {
							menu_item = menu_items.first();
						}
					}

					console.log(menu_item);

					window.location.hash = menu_item.data( 'section' );

					var form = menu_item.parents( 'form' );
					if ( form.length ) {
						var referer;
						if ( ( referer = form.find( 'input[name="_wp_http_referer"]' ) ).length ) {
							var referer_val = referer.val().split( '#' )[0];
							referer_val += '#' + menu_item.data( 'section' );
							referer.val( referer_val );
						}
					}

					menu_items.removeClass('active');
					menu_item.addClass('active');
					sections.removeClass('active');

					var section = sections.filter('[data-section="' + menu_item.data('section') + '"]'),
						descriptions = $( section ).find( '.basement_settings_panel_block_description' );
						max_width = 0;

					section.addClass('active');

					if ( descriptions.length ) {
						$.each( descriptions, function( index, description ) {
							var width = $( description ).width();
							if ( width > max_width ) {
								max_width = width;
							}
						}); 
						descriptions.width( max_width );
						$( section ).find( '.basement_settings_panel_block_inputs' ).css( 'margin-left', max_width + 40 );
					}

					createCodeEditors();
				};

				var update_setting_page_height = function() {
					setting_page.find('.basement_settings_panel_sections').css( 'min-height', menu.outerHeight() );
				};

				if ( menu_items.length == 1 ) {
					menu.hide();
					setting_page.find('.basement_settings_panel_sections').addClass( 'basement_settings_panel_sections_menu_free' );
				} else {
					menu_items.click(function() {
						show_active_section( $(this) );
						return false;
					});
				}

				show_active_section();

				setting_page.on('basement_content_changed', function() {
					update_setting_page_height();
				});

				update_setting_page_height();
			})($(setting_page));
		});
	}

	var sections = $( '.basement_settings_panel_section' );
	if ( sections.length ) {
		$.each( sections, function( index, section ) {
			
		});
	}

	// Filters
	if ( $( '.basement_admin_filter' ).length ) {
		$( ".basement_admin_filter" ).click( function() {
			var group = $( this ).data( 'filters-group' ),
				all_filters_targets_classes = [],
				active_filters_targets_classes = [];
			$( this ).toggleClass( 'active' );

			$.each( $( '.basement_admin_filter[data-filters-group="' + group + '"]' ), function( index, filter ) {
				all_filters_targets_classes.push( $( filter ).data( 'filter' ) );
				if ( $( filter ).hasClass( 'active' ) ) {
					active_filters_targets_classes.push( $( filter ).data( 'filter' ) );
				}
			});
			
			if ( active_filters_targets_classes.length ) {
				$( all_filters_targets_classes.join( ',' ) ).hide();
				$( active_filters_targets_classes.join( ',' ) ).show();
			} else {
				$( all_filters_targets_classes.join( ',' ) ).show();
			}

			return false;
		});

	}

	// Togglers
	if ( $( '.basement_admin_toggler' ).length ) {
		$( ".basement_admin_toggler" ).click( function() {
			var group = $( this ).data( 'togglers-group' ),
				target = $(this).data( 'toggler-target' );
			$( '.basement_admin_toggler[data-togglers-group="' + group + '"]' ).removeClass( 'active' );
			$( this ).toggleClass( 'active' );

			$( '.basement_admin_toggler_target[data-togglers-group="' + group + '"]').not( target ).hide();
			$( target ).show();

			return false;
		});

	}

})();
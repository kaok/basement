<?php
	/* Add get_terms an ability to be ordered by include list */
add_filter( 'get_terms_orderby', 'avantia_get_terms_orderby', 10, 2 );
/**
 * Modifies the get_terms_orderby argument if orderby == include
 *
 * @param  string $orderby Default orderby SQL string.
 * @param  array  $args    get_terms( $taxonomy, $args ) arg.
 * @return string $orderby Modified orderby SQL string.
 */
function basement_get_terms_orderby( $orderby, $args ) {
  if ( isset( $args['orderby'] ) && 'include' == $args['orderby'] ) {
		$include = implode(',', array_map( 'absint', $args['include'] ));
		$orderby = "FIELD( t.term_id, $include )";
	}
	return $orderby;
}


// This will add a field in the attachment edit screen for applying a class to the img tag.
function IMGattachment_fields($form_fields, $post) {
    $form_fields["imageClass"]["label"] = __("Image Class", BASEMENT_THEME_TEXTDOMAIN );
    $form_fields["imageClass"]["value"] = get_post_meta($post->ID, "_imageClass", true);
    return $form_fields;
}
add_filter("attachment_fields_to_edit", "IMGattachment_fields", null, 2);
function my_image_attachment_fields_save($post, $attachment) {
    if ( isset($attachment['imageClass']) )
    update_post_meta($post['ID'], '_imageClass', $attachment['imageClass']);
    return $post;
}
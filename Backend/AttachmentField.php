<?php
namespace MJUPURL\Backend;

class AttachmentField {
	public static function get_instance() {
		static $instance = null;
		if( $instance === null ) {
			$instance = new self;
		}
		return $instance;
	}

	private function __construct() {
		add_filter( "attachment_fields_to_edit", array( $this, "fields" ), 10, 2 );
	}

	public function fields( $form_fields, $post ) {
		if( get_post_meta( $post->ID, "mjupurl", true ) ) {
			$form_fields["mjupurl_orig_url"] = array(
				"label"	=> __( "Original URL", 'mjupurl' ),
				"input"	=> "html",
				"html"	=> "<input type='text' value='" . get_post_meta( $post->ID, "mjupurl_orig_url", true ) . "' readonly>",
			);
		}

		return $form_fields;
	}
}
AttachmentField::get_instance();
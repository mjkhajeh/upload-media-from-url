<?php
namespace MJUPURL;

class Utils {
	public static function get_file_path( $filename ) {
		$upload_dir = wp_upload_dir();
		if( wp_mkdir_p( $upload_dir['path'] ) ) {
			$file = "{$upload_dir['path']}/{$filename}";
		} else {
			$file = "{$upload_dir['basedir']}/{$filename}";
		}
		return $file;
	}

	public static function download( $url, $filename = '', $timeout = 9999999 ) {
		set_time_limit(0);
		include_once( ABSPATH . 'wp-admin/includes/file.php');
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );

		$url = esc_url_raw( $url );
		
		if( empty( $filename ) ) {
			$filename = wp_basename( urldecode( $url ) );
		} else {
			$ext = pathinfo( $url, PATHINFO_EXTENSION );
			$filename = "{$filename}.{$ext}";
		}
		$file = self::get_file_path( $filename );

		$tmp_file = download_url( $url, $timeout );
		if( is_wp_error( $tmp_file ) ) return $tmp_file->get_error_message();

		copy( $tmp_file, $file );
		@unlink( $tmp_file );

		// Add to attachment
		$wp_filetype = wp_check_filetype( $filename, null );
		if( !$wp_filetype ) return __( "Filetype not allowed", 'mjupurl' );

		$attachment = array(
			'post_mime_type'	=> $wp_filetype['type'],
			'post_title'		=> sanitize_file_name( $filename ),
			'post_content'		=> '',
			'post_status'		=> 'inherit'
		);
		$attach_id = wp_insert_attachment( $attachment, $file );
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
		wp_update_attachment_metadata( $attach_id, $attach_data );

		update_post_meta( $attach_id, 'mjupurl', true );
		update_post_meta( $attach_id, 'mjupurl_orig_url', $url );

		return $attach_id;
	}

	public static function is_img( $filename ) {
		$filetype = wp_check_filetype( $filename );
		if( strpos( $filetype['type'], 'image' ) === 0 ) return true;
		return false;
	}
}
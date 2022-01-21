<?php
namespace MJUPURL\Backend;

use MJUPURL\Utils as Utils;

class Page {
	PRIVATE $SUCCESS = array();
	PRIVATE $ERROR = array();
	PRIVATE $SETTINGS = array();
	PRIVATE $LINKS = array();
	PRIVATE $BULKS = array();
	PRIVATE $ATTACHMENTS = array();

	public static function get_instance() {
		static $instance = null;
		if( $instance === null ) {
			$instance = new self;
		}
		return $instance;
	}

	private function __construct() {
		add_action( 'admin_init', array( $this, 'upload' ) );
		add_action( 'admin_menu', array( $this, 'add' ) );
		add_action( 'admin_notices', array( $this, 'notices' ) );
	}

	public function upload() {
		$this->SETTINGS = array(
			'timeout'	=> 9999999
		);
		if( !empty( $_POST ) && !empty( $_POST["mjupurl"] ) && !empty( $_POST["mjupurl"]['url'] ) ) {
			if( !empty( $_POST["mjupurl_timeout"] ) ) {
				$this->SETTINGS['timeout'] = intval( sanitize_text_field( $_POST["mjupurl_timeout"] ) );
			}

			foreach( $_POST["mjupurl"]['url'] as $index => $url ) {
				if( empty( $url ) ) continue;

				$row = $index+1;
				$url = trim( $url );
				$url = str_replace( ' ', '%20', $url );
				$url = esc_url_raw( $url );

				$bulk = $url;
				$name = "";
				if( !empty( $_POST["mjupurl"]['name'][$index] ) ) {
					$name = sanitize_text_field( $_POST["mjupurl"]['name'][$index] );
					$bulk .= " : {$name}";
				}
				$this->LINKS[] = [$url, $name];
				$this->BULKS[] = $bulk;

				if( !filter_var( $url, FILTER_VALIDATE_URL ) ) {
					$this->ERROR[] = "#{$row} : {$url} : " . __( "URL is not valid", 'mjupurl' );
					continue;
				}

				$attach_id = Utils::download( $url, $name, $this->SETTINGS['timeout'] );
				if( is_string( $attach_id ) ) {
					$this->ERROR[] = "#{$row} : {$url} : {$attach_id}";
					continue;
				}
				$this->ATTACHMENTS[] = $attach_id;
			}
			if( !empty( $this->ATTACHMENTS ) ) {
				$this->SUCCESS[] = __( "All files uploaded and added to attachments", 'mjupurl' );
			}
		}
	}

	public function add() {
		add_submenu_page(
			"upload.php",						// $parent_slug:string
			__( "Upload from URL", 'mjupurl' ),	// $page_title:string
			__( "Upload from URL", 'mjupurl' ),	// $menu_title:string
			"upload_files",						// $capability:string
			"mjupurl",							// $menu_slug:string
			array( $this, 'view' )				// $function:callable
		);
	}

	public function view() {
		wp_enqueue_script( 'mjupurl-upload-page' );
		wp_enqueue_style( 'mjupurl-upload-page' );

		$links = $this->LINKS;
		$bulks = $this->BULKS;
		?>
		<div class="wrap">
			<h1><?php _e( "Upload from URL", 'mjupurl' ) ?></h1>
			<?php if( !empty( $this->ATTACHMENTS ) ) { ?>
				<div class="mjupurl_attachments_wrap">
					<ul class="mjupurl_attachments">
						<?php
						$attachments_links = array();
						foreach( $this->ATTACHMENTS as $attach_id ) {
							$edit_link	= get_edit_post_link( $attach_id );
							$link		= wp_get_attachment_url( $attach_id );
							$img		= MJUPURL_URI . "assets/img/placeholder-image.png";
							if( Utils::is_img( basename( $link ) ) ) {
								$img = $link;
							}
							$attachments_links[] = $link;
							?>
							<li class="mjupurl_attachment">
								<div>
									<a href="<?php echo $link ?>">
										<img src="<?php echo $img ?>" alt="" class="mjupurl_attachment_img">
										<p>
											<i class="dashicons dashicons-admin-links"></i><?php echo basename( $link ) ?>
										</p>
									</a>
								</div>	
								<p>
									<a href="<?php echo $edit_link ?>" class="mjupurl_attachment_edit_link"><i class="dashicons dashicons-edit"></i><?php _e( "Edit", 'mjupurl' ) ?></a>
								</p>
							</li>
						<?php } ?>
					</ul>
					<textarea id="mjupurl_attachments_links" class="ltr" rows="15" readonly><?php echo implode( PHP_EOL, $attachments_links ) ?></textarea>
				</div>
			<?php } ?>
			<div class="mjupurl_bulk_links_wrap">
				<a href="#" class="mjupurl_toggle">
					<i class="dashicons dashicons-plus-alt2"></i>
					<?php _e( "Bulk links", 'mjupurl' ) ?>
				</a>
				<div class="mjupurl_bulk_links">
					<textarea id="mjupurl_bulk_links_input" rows="15" class="regular-text ltr"><?php echo implode( PHP_EOL, $bulks ) ?></textarea>
					<span class="description"><?php _e( "Enter your links in each line", 'mjupurl' ) ?><br><?php _e( "For set filenames follow this template: URL : FILENAME", 'mjupurl' ) ?></span>
				</div>
			</div>
			<p class="description"><?php _e( 'Enter file links you want to download and add them to the media library', 'mjupurl' ) ?></p>
			<table id="mjupurl_template">
				<tbody>
					<tr>
						<th>%row%</th>
						<td>
							<input type="url" name="mjupurl[url][%index%]" class="regular-text ltr mjupurl_url">
						</td>
						<td>
							<input type="text" name="mjupurl[name][%index%]" class="regular-text mjupurl_name">
						</td>
					</tr>
				</tbody>
			</table>
			<form action="" method="post">
				<table class="form-table" id="mjupurl_upload_table">
					<thead>
						<tr>
							<th>#</th>
							<th>
								<?php _e( 'URL', 'mjupurl' ) ?>
								<p><a href="#" class="button mjupurl_add_row"><?php _e( "Add row", 'mjupurl' ) ?></a></p>
							</th>
							<th>
								<?php _e( 'Filename', 'mjupurl' ) ?>
								<p class="description"><?php _e( "Enter filenames without extension", 'mjupurl' ) ?></p>
								<p class="description"><?php _e( "Leave empty to get original filename", 'mjupurl' ) ?></p>
							</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$count = 9;
						if( !empty( $links ) ) {
							$count = count( $links )-1;
						}
						for( $index = 0; $index <= $count; $index++ ) { 
							?>
							<tr>
								<th><?php echo $index+1 ?></th>
								<td>
									<input type="url" name="mjupurl[url][<?php echo $index ?>]" class="regular-text ltr mjupurl_url" value="<?php echo !empty( $links[$index] ) ? $links[$index][0] : '' ?>">
								</td>
								<td>
									<input type="text" name="mjupurl[name][<?php echo $index ?>]" class="regular-text mjupurl_name" value="<?php echo !empty( $links[$index] ) ? $links[$index][1] : '' ?>">
								</td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
				<a href="#" class="button mjupurl_add_row"><?php _e( "Add row", 'mjupurl' ) ?></a>
				<div class="mjupurl_settings_wrap">
					<a href="#" class="mjupurl_toggle">
						<i class="dashicons dashicons-admin-tools"></i>
						<?php _e( "Settings", 'mjupurl' ) ?>
					</a>
					<div class="mjupurl_settings">
						<table class="form-table" id="mjupurl_settings_table">
							<tr>
								<th>
									<label for="mjupurl_timeout"><?php _e( "Timeout", 'mjupurl' ) ?></label>
								</th>
								<td>
									<input type="number" name="mjupurl_timeout" id="mjupurl_timeout" min="10" class="regular-text" value="<?php echo $this->SETTINGS['timeout'] ?>">
								</td>
							</tr>
						</table>
					</div>
				</div>
				<?php submit_button( __( "Upload", 'mjupurl' ) ) ?>
			</form>
		</div>
		<?php
	}

	public function notices() {
		$screen = get_current_screen();
		if( $screen->id != "media_page_mjupurl" ) return;

		if( empty( $this->SUCCESS ) && empty( $this->ERROR ) ) return;
		
		if( !empty( $this->SUCCESS ) ) {
			$success = implode( "<br>", $this->SUCCESS );
			?>
			<div class="notice notice-success is-dismissible">
				<p><?php echo $success ?></p>
			</div>
			<?php
		}

		if( !empty( $this->ERROR ) ) {
			$error = implode( "<br>", $this->ERROR );
			?>
			<div class="notice notice-error">
				<p><?php echo $error ?></p>
			</div>
			<?php
		}
	}
}
Page::get_instance();
<?php
/**
 * Class Bulk_Uploader
 *
 * @package mkdo\bulk_upload_for_binder
 */

namespace mkdo\bulk_upload_for_binder;

/**
 * If the plugin needs attention, here is where the notices are set.
 *
 * You should place warnings such as plugin dependancies here.
 */
class Bulk_Uploader {

	/**
	 * Constructor
	 */
	function __construct() {}

	/**
	 * Do Work
	 */
	public function run() {
		add_filter( 'views_edit-binder', array( $this, 'bulk_uploader' ) );
		add_action( 'wp_ajax_nopriv_submit_dropzonejs', array( $this, 'dropzonejs_upload' ) );
		add_action( 'wp_ajax_submit_dropzonejs', array( $this, 'dropzonejs_upload' ) );
	}

	/**
	 * Binder Bulk Uploader
	 *
	 * The dropzone area for binder bulk uploads.
	 */
	function bulk_uploader() {
		// If binder isnt enabled, exit.
		if ( ! defined( 'MKDO_BINDER_PREFIX' ) ) {
			return;
		}

		$url  = admin_url( 'admin-ajax.php' );
		?>
		<div id="binderUpload"><form action="<?php echo esc_url( $url );?>" class="dropzone needsclick dz-clickable" id="dropzone-wordpress-form">
			<?php wp_nonce_field( 'protect_content',  MKDO_BINDER_PREFIX . '_bulk_upload' ); ?>
			<p class="dz-message needsclick">
				<span class="note"><?php esc_html_e( 'Drop files here or click this area to upload multiple files.', 'binder' );?></span>
			</p>
			<input type='hidden' name='action' value='submit_dropzonejs'>
			</form>
		</div>
		<p><em><?php esc_html_e( 'After you have uploaded your files, refresh your browser to view them in the list below.', 'binder' );?></em></p>
		<?php
	}

	/**
	 * Dropzone JS Upload
	 *
	 * The function called by Dropzone to do the server side upload.
	 */
	function dropzonejs_upload() {

		// If binder isnt enabled, exit.
		if ( ! defined( 'MKDO_BINDER_PREFIX' ) ) {
			return;
		}

		if ( ! empty( $_FILES ) && wp_verify_nonce( $_REQUEST[ MKDO_BINDER_PREFIX . '_bulk_upload' ], 'protect_content' ) ) {

			// Get the file type of the upload.
			$arr_file_type = wp_check_filetype( basename( $_FILES['file']['name'] ) );
			$uploaded_type = $arr_file_type['type'];

			// Setup the array of supported file types.
			$supported_types = array(
				'application/pdf',
				'application/msword',
				'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
				'application/vnd.ms-excel',
				'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
				'application/vnd.ms-powerpoint',
				'application/vnd.openxmlformats-officedocument.presentationml.presentation',
				'application/rtf',
				'text/csv',
				'application/vnd.oasis.opendocument.text',
			);

			// Filter the supported types.
			$supported_types = apply_filters( MKDO_BINDER_PREFIX . '_supported_mime_types', $supported_types );

			if ( ! in_array( $uploaded_type, $supported_types, true ) ) {
				http_response_code( 500 );
				esc_html_e( 'File type not supported.', 'binder' );
				die();
			}

			$original_name = $_FILES['file']['name'];
			$post_name     = sanitize_file_name( $_FILES['file']['name'] );
			$post_name     = pathinfo( $post_name, PATHINFO_FILENAME );

			$document         = \mkdo\binder\Binder::get_latest_document_by_post_id( $post_id );
			$description      = 'File added by bulk uploader.';
			$status           = 'latest';
			$current_version  = '0.0.1';
			$folder           = \mkdo\binder\Helper::create_guid();
			$document->folder = $folder;

			// Grab the document details.
			$original_name = $_FILES['file']['name'];
			$size          = $_FILES['file']['size'];
			$type          = pathinfo( $original_name, PATHINFO_EXTENSION );
			$file_name     = \mkdo\binder\Helper::create_guid();
			$uploads_dir   = wp_upload_dir();
			$base          = apply_filters( MKDO_BINDER_PREFIX . '_document_base', WP_CONTENT_DIR . '/uploads/binder/' );
			$path          = $base . $folder;

			// Check if the type is supported. If not, throw an error.
			if ( in_array( $uploaded_type, $supported_types, true ) ) {

				// Create all the directories that we need.
				if ( ! is_dir( $base ) ) {
					mkdir( $base );
				}
				if ( ! is_dir( $path ) ) {
					mkdir( $path );
				}

				// Upload the file.
				$success = move_uploaded_file( $_FILES['file']['tmp_name'], $path . '/' . $file_name );

				// Generate an image for the document.
				$image_file = '';
				$image      = wp_get_image_editor( $path . '/' . $file_name, array( 'mime_type' => $uploaded_type ) );
				$images     = array();

				if ( ! is_wp_error( $image ) ) {

					// Add all existing sizes to the array.
					$sizes = \mkdo\binder\Helper::get_image_sizes();
					if ( ! empty( $sizes ) ) {
						foreach ( $sizes as $key => $s ) {
							$image = wp_get_image_editor( $path . '/' . $file_name, array( 'mime_type' => $uploaded_type ) );
							$image->resize( $s['width'], $s['height'], $s['crop'] );
							$image_file = $image->generate_filename( '', $path . '/', 'jpg' );
							$image->save( $image_file, 'image/jpeg' );
							$image_file     = str_replace( $path, '', $image_file );
							$image_file     = trim( $image_file, '/' );
							$images[ $key ] = $image_file;
						}
					}

					// Lets get the default size, and push this into the array.
					$image->resize( 2048, 4096, false );
					$image_file = $image->generate_filename( '', $path . '/', 'jpg' );
					$image->save( $image_file, 'image/jpeg' );
					$image_file        = str_replace( $path, '', $image_file );
					$image_file        = trim( $image_file, '/' );
					$images['default'] = $image_file;

					// Add custom sizes into the array.
					$images = apply_filters( MKDO_BINDER_PREFIX . '_custom_image_sizes', $images, $image );
				}

				$post_id = wp_insert_post(
					array(
						'post_title'  => $post_name,
						'post_status' => 'publish',
						'post_type'   => 'binder',
					)
				);

				// Convert the array to a string.
				$images = serialize( $images );

				$document->post_id     = $post_id;
				$document->upload_date = date( 'Y-m-d H:i:s' );
				$document->user_id     = get_current_user_id();
				$document->type        = $type;
				$document->status      = $status;
				$document->version     = esc_html( $current_version );
				$document->name        = $original_name;
				$document->description = wp_kses_post( $description );
				$document->folder      = $folder;
				$document->file        = $file_name;
				$document->size        = $size;
				$document->thumb       = $images;
				$document->mime_type   = $uploaded_type;

				// Get the text from the file.
				if ( 'pdf' === $type ) {
					$a = new \PDF2Text();
					$a->setFilename( $path . '/' . $file_name );
					$a->decodePDF();
					$output = $a->output();
				} else {
					$converter = new \DocxConversion(  $path . '/' . $file_name, $type );
					$output    = $converter->convertToText();
				}

				// Update the post content.
				$history = \mkdo\binder\Binder::get_history_by_post_id( $post_id );
				if ( 'draft' !== $status || 1 === count( $history ) ) {

					// Update the content.
					if ( ! empty( $output ) ) {
						$document_post               = get_post( $post_id );
						$document_post->post_content = apply_filters( 'the_content', $output );
						remove_action( 'save_post', array( $this, 'save_meta' ), 9999 );
						wp_update_post( $document_post );
						add_action( 'save_post', array( $this, 'save_meta' ), 9999 );
					}

					// Update the type.
					wp_set_object_terms( $post_id, array( $type ), 'binder_type', false );
				}
				\mkdo\binder\Binder::add_entry( $document, $post_id );
			}
		}
		die();
	}
}

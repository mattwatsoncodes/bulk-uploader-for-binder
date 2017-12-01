<?php
/**
 * Class Controller_Main
 *
 * @since	0.1.0
 *
 * @package mkdo\bulk_upload_for_binder
 */

namespace mkdo\bulk_upload_for_binder;

/**
 * The main loader for this plugin
 */
class Controller_Main {

	/**
	 * Enqueue the public and admin assets.
	 *
	 * @var 	object
	 * @access	private
	 * @since	0.1.0
	 */
	private $controller_assets;

	/**
	 * Notices on the admin screens.
	 *
	 * @var 	object
	 * @access	private
	 * @since	0.1.0
	 */
	private $notices_admin;

	/**
	 * Bulk uploader.
	 *
	 * @var 	object
	 * @access	private
	 * @since	0.1.0
	 */
	private $bulk_uploader;

	/**
	 * Constructor.
	 *
	 * @param Controller_Assets $controller_assets Enqueue the public and admin assets.
	 * @param Notices_Admin     $notices_admin     Notices on the admin screens.
	 * @param Bulk_Uploader     $bulk_uploader     Bulk uploader.
	 *
	 * @since 0.1.0
	 */
	public function __construct(
		Controller_Assets $controller_assets,
		Notices_Admin $notices_admin,
		Bulk_Uploader $bulk_uploader
	) {
		$this->controller_assets = $controller_assets;
		$this->notices_admin     = $notices_admin;
		$this->bulk_uploader     = $bulk_uploader;
	}

	/**
	 * Go.
	 *
	 * @since		0.1.0
	 */
	public function run() {
		load_plugin_textdomain(
			'bulk-upload-for-binder',
			false,
			MKDO_BULK_UPLOAD_FOR_BINDER_ROOT . '\languages'
		);

		$this->controller_assets->run();
		$this->notices_admin->run();
		$this->bulk_uploader->run();
	}
}

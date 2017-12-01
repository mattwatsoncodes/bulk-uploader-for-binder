<?php
/**
 * Bulk Upload extension for the WordPress Binder Document Management System (DMS).
 *
 * @link              https://github.com/mwtsn/bulk-upload-for-binder
 * @package           mkdo\bulk_upload_for_binder
 *
 * Plugin Name:       Bulk Upload for Binder
 * Plugin URI:        https://github.com/mwtsn/bulk-upload-for-binder
 * Description:       Bulk Upload extension for the WordPress Binder Document Management System (DMS).
 * Version:           0.1.0
 * Author:            Make Do <hello@makedo.net>
 * Author URI:        https://makedo.net
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       bulk-upload-for-binder
 * Domain Path:       /languages
 */

// Abort if this file is called directly.
if ( ! defined( 'WPINC' ) ) {
	die;
}

global $wpdb;

// Constants.
define( 'MKDO_BULK_UPLOAD_FOR_BINDER_ROOT', __FILE__ );
define( 'MKDO_BULK_UPLOAD_FOR_BINDER_NAME', 'Bulk Upload for Binder' );
define( 'MKDO_BULK_UPLOAD_FOR_BINDER_VERSION', '0.1.0' );
define( 'MKDO_BULK_UPLOAD_FOR_BINDER_PREFIX', 'mkdo_bulk_upload_for_binder' );

// Classes.
require_once 'php/class-helper.php';
require_once 'php/class-controller-assets.php';
require_once 'php/class-controller-main.php';
require_once 'php/class-notices-admin.php';
require_once 'php/class-bulk-uploader.php';

// Namespaces
//
// Add references for each class here. If you add new classes be sure to include
// the namespace.
use mkdo\bulk_upload_for_binder\Helper;
use mkdo\bulk_upload_for_binder\Controller_Assets;
use mkdo\bulk_upload_for_binder\Controller_Main;
use mkdo\bulk_upload_for_binder\Notices_Admin;
use mkdo\bulk_upload_for_binder\Bulk_Uploader;

// Instances.
$controller_assets  	        = new Controller_Assets();
$notices_admin  	            = new Notices_Admin();
$bulk_uploader  	            = new Bulk_Uploader();
$controller_main                = new Controller_Main(
	$controller_assets,
	$notices_admin,
	$bulk_uploader
);

// Go.
$controller_main->run();

register_uninstall_hook( MKDO_BULK_UPLOAD_FOR_BINDER_ROOT, 'mkdo_bulk_upload_for_binder_uninstall' );

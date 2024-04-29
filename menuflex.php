<?php

/**
 * Plugin Name: MenuFlex
 * Description: WordPress Menu Editor was never been easy with exclusive features like User Roles, Custom Icon, Live Menu Item Change and many more
 * Plugin URI: https://wpadminify.com/menu-editor/
 * Author: Jewel Theme
 * Version: 1.0.0
 * Author URI: https://wpadminify.com
 * Text Domain: menuflex
 * Domain Path: /languages
 * License:     GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */


// No, Direct access Sir !!!
if (!defined('ABSPATH')) exit;

$adminify_menu_editor_plugin_data     = get_file_data(__FILE__,  array(
    'Version'     => 'Version',
    'Plugin Name' => 'Plugin Name',
    'Author'      => 'Author',
    'Description' => 'Description',
    'Plugin URI'  => 'Plugin URI',
), false);


// Define Constants
if (!defined('MENUFLEX')) define('MENUFLEX', $adminify_menu_editor_plugin_data['Plugin Name']);
if (!defined('MENUFLEX_VER')) define('MENUFLEX_VER', $adminify_menu_editor_plugin_data['Version']);
if (!defined('MENUFLEX_FILE')) define('MENUFLEX_FILE', __FILE__);
if (!defined('MENUFLEX_BASE')) define('MENUFLEX_BASE', plugin_basename(__FILE__));
if (!defined('MENUFLEX_PATH')) define('MENUFLEX_PATH', trailingslashit(plugin_dir_path(__FILE__)));
if (!defined('MENUFLEX_URL')) define('MENUFLEX_URL', trailingslashit(plugins_url('/', __FILE__)));
if (!defined('MENUFLEX_ASSETS')) define('MENUFLEX_ASSETS', MENUFLEX_URL . 'assets/');
if (!defined('MENUFLEX_ASSETS_IMAGE')) define('MENUFLEX_ASSETS_IMAGE', MENUFLEX_ASSETS . 'images/');
if (!defined('MENUFLEX_ASSET_PATH')) define('MENUFLEX_ASSET_PATH', wp_upload_dir()['basedir'] . '/menu-editor-adminify');
if (!defined('MENUFLEX_ASSET_URL')) define('MENUFLEX_ASSET_URL', wp_upload_dir()['baseurl'] . '/menu-editor-adminify');
if (!defined('MENUFLEX_DESC')) define('MENUFLEX_DESC', $adminify_menu_editor_plugin_data['Description']);
if (!defined('MENUFLEX_AUTHOR')) define('MENUFLEX_AUTHOR', $adminify_menu_editor_plugin_data['Author']);
if (!defined('MENUFLEX_URI')) define('MENUFLEX_URI', $adminify_menu_editor_plugin_data['Plugin URI']);



if (!class_exists('\\MenuFlex\\MenuFlex')) {
    // Autoload
    require_once dirname(__FILE__) . '/vendor/autoload.php';

    // Instantiate Menuflex Class
    require_once dirname(__FILE__) . '/class-menuflex.php';

    // Activation and Deactivation hooks
    register_activation_hook(MENUFLEX_FILE, array('\\MenuFlex\\MenuFlex', 'menu_editor_adminify_activation_hook'));
    register_deactivation_hook(MENUFLEX_FILE, array('\\MenuFlex\\MenuFlex', 'menu_editor_adminify_deactivation_hook'));
}

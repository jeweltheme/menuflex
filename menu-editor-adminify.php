<?php

/**
 * Plugin Name: MenuX
 * Description: WordPress Menu Editor was never been easy with exclusive features like User Roles, Custom Icon, Live Menu Item Change and many more
 * Plugin URI: https://wpadminify.com/menu-editor/
 * Author: Jewel Theme
 * Version: 1.0.0
 * Author URI: https://wpadminify.com
 * Text Domain: menu-editor-adminify
 * Domain Path: /languages
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
if (!defined('ADMINIFY_MENU_EDITOR')) define('ADMINIFY_MENU_EDITOR', $adminify_menu_editor_plugin_data['Plugin Name']);
if (!defined('ADMINIFY_MENU_EDITOR_VER')) define('ADMINIFY_MENU_EDITOR_VER', $adminify_menu_editor_plugin_data['Version']);
if (!defined('ADMINIFY_MENU_EDITOR_TD')) define('ADMINIFY_MENU_EDITOR_TD', 'menu-editor-adminify');
if (!defined('ADMINIFY_MENU_EDITOR_FILE')) define('ADMINIFY_MENU_EDITOR_FILE', __FILE__);
if (!defined('ADMINIFY_MENU_EDITOR_BASE')) define('ADMINIFY_MENU_EDITOR_BASE', plugin_basename(__FILE__));
if (!defined('ADMINIFY_MENU_EDITOR_PATH')) define('ADMINIFY_MENU_EDITOR_PATH', trailingslashit(plugin_dir_path(__FILE__)));
if (!defined('ADMINIFY_MENU_EDITOR_URL')) define('ADMINIFY_MENU_EDITOR_URL', trailingslashit(plugins_url('/', __FILE__)));
if (!defined('ADMINIFY_MENU_EDITOR_ASSETS')) define('ADMINIFY_MENU_EDITOR_ASSETS', ADMINIFY_MENU_EDITOR_URL . 'assets/');
if (!defined('ADMINIFY_MENU_EDITOR_ASSETS_IMAGE')) define('ADMINIFY_MENU_EDITOR_ASSETS_IMAGE', ADMINIFY_MENU_EDITOR_ASSETS . 'images/');
if (!defined('ADMINIFY_MENU_EDITOR_ASSET_PATH')) define('ADMINIFY_MENU_EDITOR_ASSET_PATH', wp_upload_dir()['basedir'] . '/menu-editor-adminify');
if (!defined('ADMINIFY_MENU_EDITOR_ASSET_URL')) define('ADMINIFY_MENU_EDITOR_ASSET_URL', wp_upload_dir()['baseurl'] . '/menu-editor-adminify');
if (!defined('ADMINIFY_MENU_EDITOR_DESC')) define('ADMINIFY_MENU_EDITOR_DESC', $adminify_menu_editor_plugin_data['Description']);
if (!defined('ADMINIFY_MENU_EDITOR_AUTHOR')) define('ADMINIFY_MENU_EDITOR_AUTHOR', $adminify_menu_editor_plugin_data['Author']);
if (!defined('ADMINIFY_MENU_EDITOR_URI')) define('ADMINIFY_MENU_EDITOR_URI', $adminify_menu_editor_plugin_data['Plugin URI']);



if (!class_exists('\\MenuEditorAdminify\\Adminify_Menu_Editor')) {
    // Autoload
    require_once dirname(__FILE__) . '/vendor/autoload.php';

    // Instantiate WP Adminify Class
    require_once dirname(__FILE__) . '/class-adminify-menu-editor.php';

    // Activation and Deactivation hooks
    register_activation_hook(ADMINIFY_MENU_EDITOR_FILE, array('\\MenuEditorAdminify\\Adminify_Menu_Editor', 'menu_editor_adminify_activation_hook'));
    register_deactivation_hook(ADMINIFY_MENU_EDITOR_FILE, array('\\MenuEditorAdminify\\Adminify_Menu_Editor', 'menu_editor_adminify_deactivation_hook'));
}

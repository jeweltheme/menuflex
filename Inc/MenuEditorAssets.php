<?php

namespace MenuFlex\Inc;

// no direct access allowed
if (!defined('ABSPATH'))  exit;

/**
 * Menuflex
 * @package Menuflex: Menu Editor
 *
 * @author Menuflex <support@wpadminify.com>
 */

class MenuEditorAssets extends MenuEditorModel
{
    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'menu_editor_enqueue_scripts']);
    }

    public function menu_editor_enqueue_scripts()
    {
        // Register Styles
        wp_register_style('menuflex', MENUFLEX_ASSETS . 'css/menu-editor-adminify.css', false, MENUFLEX_VER);
        wp_register_style('menu-editor-adminify-tokenize2', MENUFLEX_ASSETS . 'vendors/tokenize/tokenize2.min.css', false, MENUFLEX_VER);
        wp_register_style('menu-editor-adminify-icon-picker', MENUFLEX_ASSETS . 'vendors/adminify-icon-picker/css/style.css', false, MENUFLEX_VER);
        wp_register_style('menu-editor-adminify-custom-menu', MENUFLEX_ASSETS . 'css/menu-editor-adminify-custom-menu.css', false, MENUFLEX_VER);

        // Font Icons
        wp_register_style('menu-editor-adminify-simple-line-icons', MENUFLEX_ASSETS . 'fonts/simple-line-icons/css/simple-line-icons.css', false, MENUFLEX_VER);
        wp_register_style('menu-editor-adminify-icomoon', MENUFLEX_ASSETS . 'fonts/icomoon/style.css', false, MENUFLEX_VER);
        wp_register_style('menu-editor-adminify-themify-icons', MENUFLEX_ASSETS . 'fonts/themify-icons/themify-icons.css', false, MENUFLEX_VER);

        // Register Scripts
        wp_register_script('menu-editor-adminify-icon-picker', MENUFLEX_ASSETS . 'vendors/adminify-icon-picker/js/adminify-icon-picker.js',  ['jquery'], MENUFLEX_VER, true);
        wp_register_script('menu-editor-adminify-tokenize2', MENUFLEX_ASSETS . 'vendors/tokenize/tokenize2.min.js', ['jquery'], MENUFLEX_VER, false);
        wp_register_script('menuflex', MENUFLEX_ASSETS . 'js/menu-editor-adminify.js',  ['jquery', 'jquery-ui-sortable', 'menu-editor-adminify-icon-picker'], MENUFLEX_VER, true);


        global $pagenow;
        if (('admin.php' === $pagenow) && ('menuflex' === $_GET['page'])) {
            $this->import_css();

            // Enqueue Styles
            wp_enqueue_style('menu-editor-adminify-icon-picker');
            wp_enqueue_style('menu-editor-adminify-tokenize2');
            wp_enqueue_style('menuflex');

            // Enqueue Scripts
            wp_enqueue_script('menu-editor-adminify-icon-picker');
            wp_enqueue_script('menu-editor-adminify-tokenize2');
            wp_enqueue_script('menuflex');
        }

        wp_enqueue_style('menu-editor-adminify-custom-menu');

        // Font Icons
        wp_enqueue_style('dashicons');
        wp_enqueue_style('menu-editor-adminify-simple-line-icons');
        wp_enqueue_style('menu-editor-adminify-icomoon');
        wp_enqueue_style('menu-editor-adminify-themify-icons');

        if (Utils::is_plugin_active('elementor/elementor.php')) {
            wp_enqueue_style('elementor-icons');
        }

        // Plugins Packaged Icons Library
        $plugins_icons = [];
        if (Utils::is_plugin_active('elementor/elementor.php')) {
            $plugins_icons[] = 'elementor-icons';
        }

        // Localize Scripts
        $localize_menu_data = array(
            'resturl'          => get_rest_url() . 'wpadminify/v2/',
            'ajax_url'         => admin_url('admin-ajax.php'),
            'assets_manager'   => !empty($this->options['adminify_assets']) ? $this->options['adminify_assets'] : '',
            'plugins_icons'    => $plugins_icons,
            'icon_picker_logo' => MENUFLEX_ASSETS_IMAGE . 'menu-icon.png',
            'security'         => wp_create_nonce('menu-editor-adminify-security-nonce'),
        );
        wp_localize_script('menuflex', 'MenuFlex', $localize_menu_data);
    }

    /**Import Menu CSS */
    public function import_css()
    {
        $menu_editor_custom_css = '';

        // echo '<style type="text/css">';
        $menu_editor_custom_css .= '.dropdown-content{
            position          : relative;
        }
        #adminify_import_menu{
            cursor            : pointer;
            overflow          : hidden;
            font-size         : 500px;
            position          : absolute;
            top               : 38px;
            z-index           : 1;
            width             : 100%;
            height            : 30px;
            left              : 0;
            -webkit-appearance: none;
            opacity           : 0;
            cursor            : pointer;
        }
        .icon-picker-container {
            position          : absolute;
            width             : 550px;
            height            : 290px;
            font-size         : 14px;
            background-color  : #fff;
            -webkit-box-shadow: 0 1px 5px rgba(0, 0, 0, 0.1);
            box-shadow        : 0 1px 2px rgba(0, 0, 0, 0.1);
            overflow          : hidden;
            padding           : 5px;
            box-sizing        : border-box;
            z-index           : 9999;
        }
        .icon-picker-container {
            margin-left: -220px;
            margin-top : 50px;
            width      : 30%;
            z-index    : 999999 !important;
        }
        li.jltma-icommon {
            margin: 3px 3px !important;
        }
        li.jltma-icommon a {
            border : none !important;
            padding: 1px 2px !important;
        }
        .icon-picker-container ul li.jltma-icommon a:hover{
            background: none !important;
        }

        .icon-picker-container ul {
            margin       : 0;
            padding      : 0;
            margin-top   : 8px;
            margin-bottom: 10px;
        }
        .icon-picker-container ul li a span {
            width     : 20px;
            height    : 20px;
            font-size : 20px;
            display   : block;
            text-align: left;
        }
        .icon-picker-container ul li {
            display: inline-block;
            margin : 5px;
            float  : left;
        }
        .icon-picker-container ul li a {
            display        : block;
            text-decoration: none;
            color          : #373737;
            padding        : 6px 10px;
            border         : 1px solid #eee;
        }
        .icon-picker-container ul li a:hover {
            border-color: #999;
            background  : #efefef;
        }
        .icon-picker-control {
            height: 32px;
            height: 64px;
        }
        .icon-picker-control a {
            padding        : 5px;
            text-decoration: none;
            line-height    : 32px;
            width          : 25px;
        }
        .icon-picker-control a span {
            display       : inline;
            vertical-align: middle;
        }
        .icon-picker-control input {
            width: 200px;
        }
        .icon-picker-control p {
            text-align: left;
            margin    : 0;
            padding   : 3px 10px;
        }
        .icon-picker-control select {
            margin : 0 auto;
            display: inline-block;
            width  : auto;
        }
        /* DIV Button with Preview */
        div.button.icon-picker {
            font-size  : 24px;
            height     : 30px;
            width      : 30px;
            margin     : 0;
            padding    : 0;
            line-height: 30px;
            text-align : center;
        }
        .button.icon-picker:before{
            content    : "\f504";
            font-family: dashicons;
            font-size  : 30px;
        }
        .icon-picker-close{
            float      : right;
            display    : inline-block;
            padding    : 2px;
            background : #ccc;
            cursor     : pointer;
            font-weight: 600;
        }
        .jltma-pro-badge{
            position    : absolute;
            z-index     : 333;
            text-align  : center;
            padding-left: 23%;
            font-size   : 70px !important;
            padding-top : 10%;
        }
        .top-badge{
            padding-left: 20%;
            padding-top : 0;
        }
        .jltma-disabled{
            pointer-events: none;
            opacity       : 0.4;
        }';


        // Combine the values from above and minifiy them.
        $menu_editor_custom_css = preg_replace('#/\*.*?\*/#s', '', $menu_editor_custom_css);
        $menu_editor_custom_css = preg_replace('/\s*([{}|:;,])\s+/', '$1', $menu_editor_custom_css);
        $menu_editor_custom_css = preg_replace('/\s\s+(.*)/', '$1', $menu_editor_custom_css);

        wp_add_inline_style('wp-adminify-menu-editor', wp_strip_all_tags($menu_editor_custom_css));
    }
}

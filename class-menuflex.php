<?php

namespace MenuFlex;

use MenuFlex\Inc\MenuEditor;

// No, Direct access Sir !!!
if (!defined('ABSPATH')) exit;

if (!class_exists('MenuFlex')) {

    class MenuFlex
    {

        const VERSION = ADMINIFY_MENU_EDITOR_VER;
        private static $instance = null;


        public function __construct()
        {
            add_filter('admin_body_class', [$this, 'adminify_menu_editor_body_class']);
            $this->adminify_menu_editor_is_plugin_row_meta();
            new MenuEditor();
        }

        /**
         * Add Body Class
         */
        public function adminify_menu_editor_body_class($classes)
        {
            $classes .= ' menu-editor-adminify ';

            if (is_rtl()) {
                $classes .= ' menu-editor-adminify-rtl ';
            }
            return $classes;
        }

        // Plugin Row Meta
        public function adminify_menu_editor_is_plugin_row_meta()
        {
            add_filter('plugin_row_meta', [$this, 'adminify_menu_editor_plugin_row_meta'], 10, 2);
            add_filter('network_admin_plugin_row_meta', [$this, 'adminify_menu_editor_plugin_row_meta'], 10, 2);
        }

        // Plugin Row Meta Links
        public function adminify_menu_editor_plugin_row_meta($plugin_meta, $plugin_file)
        {
            if (ADMINIFY_MENU_EDITOR_BASE === $plugin_file) {
                $row_meta = [
                    'docs' => sprintf(
                        '<a href="%1$s" target="_blank">%2$s</a>',
                        esc_url_raw('https://wpadminify.com/kb'),
                        __('Docs', 'menuflex')
                    ),
                    'changelogs' => sprintf(
                        '<a href="%1$s" target="_blank">%2$s</a>',
                        esc_url_raw('https://wpadminify.com/changelogs/'),
                        __('Changelogs', 'menuflex')
                    ),
                    'tutorials' => '<a href="https://www.youtube.com/playlist?list=PLqpMw0NsHXV-EKj9Xm1DMGa6FGniHHly8" aria-label="' . esc_attr(__('View WP Adminify Video Tutorials', 'menuflex')) . '" target="_blank">' . __('Video Tutorials', 'menuflex') . '</a>',
                ];

                $plugin_meta = array_merge($plugin_meta, $row_meta);
            }

            return $plugin_meta;
        }

        // Init Plugin
        public function adminify_menu_editor_init()
        {
            $this->adminify_menu_editor_load_textdomain();
        }

        // Text Domains
        public function adminify_menu_editor_load_textdomain()
        {
            $domain = 'menuflex';
            $locale = apply_filters('plugin_locale', get_locale(), $domain);

            load_textdomain($domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo');
            load_plugin_textdomain($domain, false, dirname(ADMINIFY_MENU_EDITOR_BASE) . '/languages/');
        }



        // Activation Hook
        public static function menu_editor_adminify_activation_hook()
        {
            $current_adminify_version = get_option('menu_editor_adminify_version', null);

            if (get_option('adminify_menu_editor_activation_time') === false)
                update_option('adminify_menu_editor_activation_time', strtotime("now"));

            if (is_null($current_adminify_version)) {
                update_option('menu_editor_adminify_version', self::VERSION);
            }
        }


        // Deactivation Hook
        public static function menu_editor_adminify_deactivation_hook()
        {
            delete_option('adminify_menu_editor_activation_time');
            delete_option('adminify_menu_editor_customizer_flush_url');
        }

        /**
         * Returns the singleton instance of the class.
         */

        public static function get_instance()
        {
            if (!isset(self::$instance) && !(self::$instance instanceof MenuFlex)) {
                self::$instance = new MenuFlex();
                self::$instance->adminify_menu_editor_init();
            }

            return self::$instance;
        }
    }
    MenuFlex::get_instance();
}

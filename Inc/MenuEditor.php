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

if (!class_exists('MenuEditor')) {
    class MenuEditor extends MenuEditorModel
    {
        public $url;
        public $menu;
        public $users;
        public $roles;
        public $submenu;
        public $menu_settings;

        public function __construct()
        {
            $this->url = MENUFLEX_URL;
            $this->menu_settings = (new MenuEditorOptions())->get();

            add_action('admin_menu', [$this, 'adminify_menu_editor_page']);
            add_filter('admin_body_class', [$this, 'jltwp_adminify_menu_editor_body_class']);

            add_filter('parent_file', [$this, 'set_menu'], 800);
            add_filter('parent_file', [$this, 'apply_menu'], 900);

            add_action('wp_ajax_menu_editor_adminify_save_menu_settings', [$this, 'menu_editor_adminify_save_menu_settings']);
            add_action('wp_ajax_menu_editor_adminify_reset_menu_settings', [$this, 'menu_editor_adminify_reset_menu_settings']);
            add_action('wp_ajax_adminify_export_menu_settings', [$this, 'adminify_export_menu_settings']);
            add_action('wp_ajax_adminify_import_menu_settings', [$this, 'adminify_import_menu_settings']);

            new MenuEditorAssets();
            new VerticalMenu();
        }

        // Menu Editor Body Class
        public function jltwp_adminify_menu_editor_body_class($classes)
        {
            $classes .= ' adminify_menu_editor ';
            return $classes;
        }


        /**
         * Sanitises and strips tags of input from ajax
         * @since 1.0.0
         * @variables $values = item to clean (array or string)
         */
        public function clean_ajax_input($values)
        {

            if (is_array($values)) {
                foreach ($values as $index => $in) {
                    if (is_array($in)) {
                        $values[$index] = $this->clean_ajax_input($in);
                    } else {
                        $values[$index] = strip_tags($in);
                    }
                }
            } else {
                $values = strip_tags($values);
            }

            return $values;
        }


        /**
         * Returns ajax error
         * @since 1.4
         * @variables $message = error message to send back to user (string)
         */
        public function ajax_error_message($message)
        {
            $returndata = array();
            $returndata['error'] = true;
            $returndata['error_message'] = $message;
            return wp_json_encode($returndata);
        }



        public function menu_editor_adminify_save_menu_settings()
        {

            if (defined('DOING_AJAX') && DOING_AJAX && check_ajax_referer('menu-editor-adminify-security-nonce', 'security') > 0) {

                $options = wp_kses_post_deep(wp_unslash($_POST['options']));
                $options =  $this->clean_ajax_input($options);

                if ($options == "" || !is_array($options)) {
                    $message = __("No options supplied to save", 'menuflex');
                    $this->ajax_error_message($message);
                    die();
                }

                if (is_array($options)) {
                    update_option($this->prefix, $options);
                    $returndata = array();
                    $returndata['success'] = true;
                    $returndata['message'] = __('Settings saved', 'menuflex');
                    echo wp_json_encode($returndata);
                    die();
                } else {
                    $message = __("Something went wrong", 'menuflex');
                    $this->ajax_error_message($message);
                    die();
                }
            }
            die();
        }


        /**
         * Menu Editor Settings Reset
         *
         * @return void
         */
        public function menu_editor_adminify_reset_menu_settings()
        {
            if (defined('DOING_AJAX') && DOING_AJAX && check_ajax_referer('menu-editor-adminify-security-nonce', 'security') > 0) {

                update_option($this->prefix, array());
                $menu_editor_options = get_option($this->prefix);

                if (!$menu_editor_options) {
                    $returndata = array();
                    $returndata['success'] = true;
                    $returndata['message'] = __('Settings reset', 'menuflex');
                    echo wp_json_encode($returndata);
                    die();
                } else {
                    $message = __("Something went wrong", 'menuflex');
                    $this->ajax_error_message($message);
                    die();
                }
            }
            die();
        }

        /**
         * Export Menu Editor Settings
         *
         * @return void
         */
        public function adminify_export_menu_settings()
        {
            if (defined('DOING_AJAX') && DOING_AJAX && check_ajax_referer('menu-editor-adminify-security-nonce', 'security') > 0) {
                $menu_editor_options = get_option($this->prefix);
                echo wp_json_encode($menu_editor_options);
            }
            die();
        }


        /**
         * Import Menu Editor Settings
         * @since 1.0.0
         */

        public function adminify_import_menu_settings()
        {
            if (defined('DOING_AJAX') && DOING_AJAX && check_ajax_referer('menu-editor-adminify-security-nonce', 'security') > 0) {

                $settings = wp_kses_post_deep(wp_unslash($_POST['settings']));
                $new_options = $this->clean_ajax_input($settings);

                if (is_array($new_options)) {
                    update_option($this->prefix, $new_options);
                }

                echo esc_html__("Menu Imported", 'menuflex');
            }
            die();
        }


        /**
         * Get menu items
         *
         * @param [type] $parent_file
         *
         * @return void
         */
        public function set_menu($parent_file)
        {
            global $menu, $submenu;
            $this->menu = $this->sort_menu_settings($menu);
            $this->submenu = $this->sort_sub_menu_settings($this->menu, $submenu);
            return $parent_file;
        }


        /**
         * Sorts Menu's for Option settings
         */
        public function sort_menu_settings($thismenu)
        {

            $menu_settings = $this->menu_settings;
            $tempmenu = array();

            foreach ($thismenu as $key => $current_menu_item) {

                $optiongroup = array();
                $order = $key;

                if (is_array($menu_settings)) {

                    if (isset($menu_settings[$current_menu_item[2]])) {
                        $optiongroup = $menu_settings[$current_menu_item[2]];

                        if (isset($optiongroup['order'])) {
                            $order = $optiongroup['order'];
                        }
                    }
                }

                $current_menu_item['order'] = $order;

                array_push($tempmenu, $current_menu_item);
            }

            return $this->sort_array($tempmenu);
        }


        /**
         * usort function for menu arrays
         */

        public function sort_array_helper($a, $b)
        {
            $result = 0;
            if (!isset($a['order'])) {
                return $result;
            }
            if ($a['order'] > $b['order']) {
                $result = 1;
            } else if ($a['order'] < $b['order']) {
                $result = -1;
            }
            return $result;
        }


        /**
         * Sorts arrays by key 'order'
         */
        public function sort_array($tosort)
        {
            usort($tosort, [$this, "sort_array_helper"]);
            return $tosort;
        }
        /**
         * Sorts Sub Menu for settings
         * @since 1.4
         */

        public function sort_sub_menu_settings($themenu, $thesubmenu)
        {

            $menu_settings = $this->menu_settings;

            $tempsubmenu = array();

            foreach ($themenu as $current_menu_item) {

                $optiongroup = array();
                $submenu_items = array();

                if (isset($thesubmenu[$current_menu_item[2]])) {

                    $submenuitems = $thesubmenu[$current_menu_item[2]];

                    foreach ($submenuitems as $key => $subitem) {

                        $subitem['order'] = $key;

                        if (is_array($menu_settings) && isset($menu_settings[$current_menu_item[2]]) && isset($menu_settings[$current_menu_item[2]]['submenu'])) {

                            $submenugroup = $menu_settings[$current_menu_item[2]]['submenu'];

                            if (isset($submenugroup[$subitem[2]])) {

                                $itemoptions = $submenugroup[$subitem[2]];

                                if (isset($itemoptions['order'])) {

                                    $subitem['order'] = $itemoptions['order'];
                                }
                            }
                        }

                        array_push($submenu_items, $subitem);
                    }

                    $submenu_items = $this->sort_array($submenu_items);

                    $tempsubmenu[$current_menu_item[2]] = $submenu_items;
                }
            }

            return $tempsubmenu;
        }


        /**
         * Applies menu settings
         */
        public function apply_menu($parent_file)
        {
            global $menu, $submenu;
            $tempmenu = array();
            $tempsub = array();
            $submenu = $this->sort_sub_menu_settings($menu, $submenu);

            if ($menu && is_array($menu)) {

                foreach ($menu as $key => $menu_item) {

                    if (strpos($menu_item[2], "separator") !== false  && !$menu_item[0]) {

                        // Build Separator
                        $newitem = $this->apply_separator_settings($menu_item, $key);
                        if ($newitem) {
                            array_push($tempmenu, $newitem);
                        }
                    } else {

                        // Build Top Level
                        $newitem = $this->apply_top_level_settings($menu_item, $key);
                        if ($newitem) {
                            array_push($tempmenu, $newitem);

                            if (isset($submenu[$newitem[2]])) {

                                $subitem = $this->apply_sub_level_settings($submenu[$newitem[2]], $newitem[2]);

                                if ($subitem) {
                                    $tempsub[$newitem[2]] = $this->apply_sub_level_settings($submenu[$newitem[2]], $newitem[2]);
                                }
                            }
                        }
                    }
                }
            }

            $submenu = $tempsub;
            $menu = $this->sort_array($tempmenu);

            return $parent_file;
        }


        /**
         * Applies top level menu item settings
         * @since 1.4
         */

        public function apply_sub_level_settings($subitems, $parentname)
        {

            if (!is_array($this->menu_settings)) {
                return $subitems;
            }

            if (!isset($this->menu_settings[$parentname]['submenu'])) {
                return $subitems;
            }

            $submenu_settings = $this->menu_settings[$parentname]['submenu'];

            $tempsub = array();

            foreach ($subitems as $current_menu_item) {
                $name = '';
                $link = '';
                $disabled_for = array();
                $optiongroup = array();

                ///NO SETTINGS
                if (!isset($submenu_settings[$current_menu_item[2]])) {
                    array_push($tempsub, $current_menu_item);
                    continue;
                }

                $optiongroup = $submenu_settings[$current_menu_item[2]];

                if (isset($optiongroup['name'])) {
                    $name = $optiongroup['name'];

                    if ($name != "") {
                        $current_menu_item[0] = $name;
                    }
                }

                if (isset($optiongroup['link'])) {
                    $link = $optiongroup['link'];

                    if ($link != "") {
                        $current_menu_item[2] = $link;
                        $current_menu_item['link'] = $link;
                    }
                }

                if (isset($optiongroup['hidden_for'])) {
                    $disabled_for = $optiongroup['hidden_for'];

                    if ($this->is_hidden($disabled_for)) {
                        $current_menu_item['hidden'] = true;
                        continue;
                    }
                }

                array_push($tempsub, $current_menu_item);
            }

            if (count($tempsub) < 1) {
                return false;
            } else {
                return $tempsub;
            }
        }


        /**
         * Applies top level menu item settings
         * @since 1.4
         */

        public function apply_top_level_settings($current_menu_item, $key)
        {
            $name = '';
            $link = '';
            $icon = '';
            $disabled_for = array();
            $optiongroup = array();
            $order = $key;

            if (is_array($this->menu_settings)) {

                if (isset($this->menu_settings[$current_menu_item[2]])) {
                    $optiongroup = $this->menu_settings[$current_menu_item[2]];

                    if (isset($optiongroup['name'])) {
                        $name = $optiongroup['name'];

                        if ($name != "") {
                            $current_menu_item[0] = $name;
                        }
                    }

                    if (isset($optiongroup['link'])) {
                        $link = $optiongroup['link'];

                        if ($link != "") {
                            $current_menu_item[2] = $link;
                            $current_menu_item['link'] = $link;
                        }
                    }

                    if (isset($optiongroup['icon'])) {
                        $icon = $optiongroup['icon'];

                        if ($icon != "") {
                            $current_menu_item['icon'] = $icon;
                        }
                    }

                    if (isset($optiongroup['order'])) {
                        $order = $optiongroup['order'];
                    }

                    if (isset($optiongroup['hidden_for'])) {
                        $disabled_for = $optiongroup['hidden_for'];

                        if ($this->is_hidden($disabled_for)) {
                            $current_menu_item['hidden'] = true;
                        }
                    }
                }
            }

            $current_menu_item['order'] = $order;

            if (isset($current_menu_item['hidden'])) {

                if ($current_menu_item['hidden'] == true) {

                    return false;
                } else {

                    return $current_menu_item;
                }
            } else {

                return $current_menu_item;
            }
        }


        /**
         * Hidden for method
         *
         * @param [type] $disabled_for
         *
         * @return boolean
         */
        public function is_hidden($disabled_for)
        {

            if (!is_array($disabled_for)) {
                return false;
            }

            $current_user = wp_get_current_user();
            $current_name = $current_user->display_name;
            $current_roles = $current_user->roles;
            $all_roles = wp_roles()->get_names();


            if (in_array($current_name, $disabled_for)) {
                return true;
            }


            ///MULTISITE SUPER ADMIN
            if (is_super_admin() && is_multisite()) {
                if (in_array('Super Admin', $disabled_for)) {
                    return true;
                } else {
                    return false;
                }
            }

            ///NORMAL SUPER ADMIN
            if ($current_user->ID === 1) {
                if (in_array('Super Admin', $disabled_for)) {
                    return true;
                } else {
                    return false;
                }
            }

            foreach ($current_roles as $role) {

                $role_name = $all_roles[$role];

                if (in_array($role_name, $disabled_for)) {
                    return true;
                }
            }
        }


        /**
         * Applies separator menu item settings
         * @since 1.0.0
         */

        public function apply_separator_settings($current_menu_item, $key)
        {
            $name = '';
            $disabled_for = array();
            $optiongroup = array();
            $order = $key;

            if (is_array($this->menu_settings)) {

                if (isset($this->menu_settings[$current_menu_item[2]])) {
                    $optiongroup = $this->menu_settings[$current_menu_item[2]];

                    if (isset($optiongroup['name'])) {
                        $name = $optiongroup['name'];

                        if ($name != "") {
                            $current_menu_item['name'] = $name;
                        }
                    }

                    if (isset($optiongroup['order'])) {
                        $order = $optiongroup['order'];
                    }

                    if (isset($optiongroup['hidden_for'])) {
                        $disabled_for = $optiongroup['hidden_for'];

                        if ($this->is_hidden($disabled_for)) {
                            $current_menu_item['hidden'] = true;
                        }
                    }
                }
            }

            $current_menu_item['order'] = $order;


            if (isset($current_menu_item['hidden'])) {

                if ($current_menu_item['hidden'] == true) {

                    return false;
                } else {

                    return $current_menu_item;
                }
            } else {

                return $current_menu_item;
            }
        }



        /**
         * Menu Editor Menu
         */
        public function adminify_menu_editor_page()
        {
            add_menu_page(
                esc_html__('Menu Editor by WP Adminify', 'menuflex'),
                esc_html__('Menu Editor', 'menuflex'),
                apply_filters('menuflex_capability', 'manage_options'),
                'menuflex',
                [$this, 'menuflex_menu_editor_contents'],
                MENUFLEX_ASSETS_IMAGE . 'menu-icon.png',
                30
            );
        }


        /**
         * Render Menu Editor
         */
        public function render_menu_editor()
        {
            global $wp_roles;

            $users = get_users();
            $this->users = $users;
            $this->roles = $wp_roles->roles;

            if ($this->menu && is_array($this->menu)) {

                foreach ($this->menu as $menu_item) {

                    if (strpos($menu_item[2], "separator") !== false  && !$menu_item[0]) {

                        // Render Separator
                        $this->render_menu_separator($menu_item);
                    } else {

                        // Render Top Level menu
                        $this->render_top_level_menu_item($this->menu, $menu_item, $this->submenu);
                    }
                }
?>
                <script>
                    jQuery(function($) {

                        $('.adminify-menu-settings').tokenize2({
                            placeholder: '<?php echo esc_html__('Select roles or users', 'menuflex') ?>'
                        });

                        $('.adminify-menu-settings').on('tokenize:select', function() {
                            $(this).tokenize2().trigger('tokenize:search', [$(this).tokenize2().input.val()]);
                        });

                    });
                </script>
            <?php

            }
        }


        public function get_icon($value, $default)
        {
            if (empty($value)) return $default;
            return $value;
        }


        /**
         * Render Top Level menu Item
         *
         * @return void
         */
        public function render_top_level_menu_item($master_menu, $current_menu_item, $master_sub_menu)
        {

            $disabled_for = '';
            $menu_id = preg_replace("/[^A-Za-z0-9 ]/", '', $current_menu_item[5]);
            $name = '';
            $link = '';
            $icon = '';
            $disabled_for = array();
            $optiongroup = array();
            $menu_options = $this->menu_settings;

            if (is_array($menu_options)) {

                if (isset($menu_options[$current_menu_item[2]])) {
                    $optiongroup = $menu_options[$current_menu_item[2]];

                    if (isset($optiongroup['name'])) {
                        $name = $optiongroup['name'];
                    }

                    if (isset($optiongroup['link'])) {
                        $link = $optiongroup['link'];
                    }
                    if (isset($optiongroup['icon'])) {
                        $icon = $optiongroup['icon'];
                    }
                    if (isset($optiongroup['hidden_for'])) {
                        $disabled_for = $optiongroup['hidden_for'];
                    }
                }
            }

            if (!is_array($disabled_for)) {
                $disabled_for = array();
            }


            /// LIST OF AVAILABLE MENU ICONS
            $icons = array(
                'dashicons-admin-multisite'  => 'dashicons dashicons-admin-multisite',
                'dashicons-dashboard'        => 'dashicons dashicons-dashboard',
                'dashicons-admin-post'       => 'dashicons dashicons-admin-post',
                'dashicons-database'         => 'dashicons dashicons-database',
                'dashicons-admin-media'      => 'dashicons dashicons-admin-media',
                'dashicons-admin-page'       => 'dashicons dashicons-admin-page',
                'dashicons-admin-comments'   => 'dashicons dashicons-admin-comments',
                'dashicons-admin-appearance' => 'dashicons dashicons-admin-appearance',
                'dashicons-admin-plugins'    => 'dashicons dashicons-admin-plugins',
                'dashicons-admin-users'      => 'dashicons dashicons-admin-users',
                'dashicons-admin-tools'      => 'dashicons dashicons-admin-tools',
                'dashicons-chart-bar'        => 'dashicons dashicons-chart-bar',
                'dashicons-admin-settings'   => 'dashicons dashicons-admin-settings',
                MENUFLEX_ASSETS_IMAGE . 'menu-icon.png'            => MENUFLEX_ASSETS_IMAGE . 'menu-icon.png'
            );

            $default_icons = (isset($current_menu_item)) ? $current_menu_item[6] : 'dashicons dashicons-external';

            ?>
            <div class="accordion adminify_menu_item" name="<?php echo esc_attr($current_menu_item[2]); ?>" id="menu-editor-adminify-top-menu-<?php echo esc_attr($menu_id); ?>">
                <input type="number" class="top_level_order" value="" style="display:none;">
                <a class="menu-editor-title accordion-button p-4" href="#">
                    <?php
                        $this->accordion_icon();
                        echo wp_kses_post(preg_replace('/\<span.*?>.*?\<\/span><\/span>/s', '', $current_menu_item[0]));
                    ?>
                </a>

                <div class="accordion-body toplevel_page_menu-editor-adminify">
                    <div class="tabs tabbable m-0">
                        <ul class="m-0 b-0 nav nav-tabs">
                            <li class="nav-item active">
                                <a class="nav-link is-clickable active" href="#tab-<?php echo esc_attr($menu_id); ?>-1">
                                    <?php echo esc_html__('Settings', 'menuflex'); ?>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link is-clickable" href="#tab-<?php echo esc_attr($menu_id); ?>-2">
                                    <?php echo esc_html__('Submenu', 'menuflex'); ?>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="tab-content tab-panel panel p-4">
                        <div id="tab-<?php echo esc_attr($menu_id); ?>-1" class="tab-pane">
                            <div class="menu-editor-form">
                                <div class="columns">
                                    <div class="column">
                                        <label for="<?php echo esc_attr($current_menu_item[2]); ?>">
                                            <?php echo esc_html__('Rename as', 'menuflex'); ?>
                                        </label>
                                        <input class="menu_setting" type="text" name="name" data-top-menu-id="<?php echo esc_attr($menu_id); ?>" placeholder="<?php echo esc_attr($current_menu_item[0]); ?>" value='<?php echo esc_attr($name); ?>' />
                                    </div>
                                    <div class="column">
                                        <label for="<?php echo esc_attr($current_menu_item[2]); ?>">
                                            <?php echo esc_html__('Hidden For Rules', 'menuflex'); ?>
                                        </label>

                                        <div class="select is-small">
                                            <select class="adminify-menu-settings menu_setting" name="hidden_for" id="<?php echo esc_attr($menu_id); ?>-user-role-types" multiple>
                                                <?php
                                                $sel = '';

                                                if (in_array('Super Admin', $disabled_for)) {
                                                    $sel = 'selected';
                                                }
                                                ?>
                                                <option value="Super Admin" <?php echo esc_attr($sel); ?>><?php echo esc_html__('Super Admin', 'menuflex') ?></option>
                                                <?php
                                                foreach ($this->roles as $role) {
                                                    $rolename = $role['name'];
                                                    $sel = '';

                                                    if (in_array($rolename, $disabled_for)) {
                                                        $sel = 'selected';
                                                    }
                                                ?>
                                                    <option value="<?php echo esc_attr($rolename); ?>" <?php echo esc_attr($sel); ?>><?php echo esc_html($rolename); ?></option>
                                                <?php
                                                }

                                                foreach ($this->users as $user) {
                                                    $username = $user->display_name;
                                                    $sel = '';

                                                    if (in_array($username, $disabled_for)) {
                                                        $sel = 'selected';
                                                    }
                                                ?>
                                                    <option value="<?php echo esc_attr($username); ?>" <?php echo esc_attr($sel); ?>><?php echo esc_html($username); ?></option>
                                                <?php
                                                }
                                                ?>
                                            </select>

                                        </div>
                                    </div>
                                </div>
                                <div class="columns">
                                    <div class="column">
                                        <label for="">
                                            <?php echo esc_html__('Change Link', 'menuflex'); ?>
                                        </label>
                                        <input class="menu_setting" name="link" type="url" placeholder="<?php echo esc_html__('New link', 'menuflex'); ?>" value="<?php echo esc_url($link); ?>">
                                    </div>
                                    <div class="column">
                                        <label for="">
                                            <?php echo esc_html__('Set Custom Icon', 'menuflex'); ?>
                                        </label>
                                        <div class="menu-editor-adminify-icon-picker-wrap menu-editor-adminify-menu-icon-picker adminify-icon-picker-input icon-select-button is-clickable is-pulled-left">
                                            <ul class="icon-picker">
                                                <li class="icon-none" title="None"><i class="dashicons dashicons-dismiss"></i></li>
                                                <li class="select-icon" title="Icon Library">
                                                    <?php
                                                    if (empty($icons[$default_icons])) {
                                                        $adminify_icon = MENUFLEX_ASSETS_IMAGE . 'menu-icon.png';
                                                        echo '<i class=""><img src=' . esc_url( $adminify_icon ) . ' ></i>';
                                                    } else { ?>
                                                        <i class="<?php echo esc_attr($this->get_icon($icon, $icons[$default_icons]) ); ?>"></i>
                                                    <?php } ?>

                                                </li>
                                                <input type="hidden" class="menu_setting" name="icon" value="<?php echo esc_attr($icon); ?>">
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="tab-<?php echo esc_attr($menu_id); ?>-2" class="tab-pane tab-pane--submenu">
                            <?php

                            // Sub Menu Items Check
                            $link = $current_menu_item[2];
                            if (isset($master_sub_menu[$link]) && is_array($master_sub_menu[$link])) {
                                foreach ($master_sub_menu[$link] as $sub_menu_item) {
                                    $this->build_sub_menu_item($sub_menu_item, $optiongroup);
                                }
                            } else {
                            ?>
                                <span><?php echo esc_html__('No sub menu items', 'menuflex'); ?></span>
                            <?php
                            } ?>

                        </div>
                    </div>

                </div>
            </div>

        <?php
        }


        public function accordion_icon(){
            return '<svg class="drag-icon is-pulled-left mr-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 14C13.1046 14 14 13.1046 14 12C14 10.8954 13.1046 10 12 10C10.8954 10 10 10.8954 10 12C10 13.1046 10.8954 14 12 14Z" fill="#4E4B66" fill-opacity="0.72" />
                    <path d="M12 7C13.1046 7 14 6.10457 14 5C14 3.89543 13.1046 3 12 3C10.8954 3 10 3.89543 10 5C10 6.10457 10.8954 7 12 7Z" fill="#4E4B66" fill-opacity="0.72" />
                    <path d="M12 21C13.1046 21 14 20.1046 14 19C14 17.8954 13.1046 17 12 17C10.8954 17 10 17.8954 10 19C10 20.1046 10.8954 21 12 21Z" fill="#4E4B66" fill-opacity="0.72" />
                    <path d="M5 14C6.10457 14 7 13.1046 7 12C7 10.8954 6.10457 10 5 10C3.89543 10 3 10.8954 3 12C3 13.1046 3.89543 14 5 14Z" fill="#4E4B66" fill-opacity="0.72" />
                    <path d="M5 7C6.10457 7 7 6.10457 7 5C7 3.89543 6.10457 3 5 3C3.89543 3 3 3.89543 3 5C3 6.10457 3.89543 7 5 7Z" fill="#4E4B66" fill-opacity="0.72" />
                    <path d="M5 21C6.10457 21 7 20.1046 7 19C7 17.8954 6.10457 17 5 17C3.89543 17 3 17.8954 3 19C3 20.1046 3.89543 21 5 21Z" fill="#4E4B66" fill-opacity="0.72" />
                    <path d="M19 14C20.1046 14 21 13.1046 21 12C21 10.8954 20.1046 10 19 10C17.8954 10 17 10.8954 17 12C17 13.1046 17.8954 14 19 14Z" fill="#4E4B66" fill-opacity="0.72" />
                    <path d="M19 7C20.1046 7 21 6.10457 21 5C21 3.89543 20.1046 3 19 3C17.8954 3 17 3.89543 17 5C17 6.10457 17.8954 7 19 7Z" fill="#4E4B66" fill-opacity="0.72" />
                    <path d="M19 21C20.1046 21 21 20.1046 21 19C21 17.8954 20.1046 17 19 17C17.8954 17 17 17.8954 17 19C17 20.1046 17.8954 21 19 21Z" fill="#4E4B66" fill-opacity="0.72" />
                </svg>';
        }


        public function render_menu_separator($current_menu_item)
        {

            $disabled_for = array();
            $name = '';
            $menu_id = preg_replace("/[^A-Za-z0-9 ]/", '', $current_menu_item[2]);
            $menu_options = $this->menu_settings;

            if (is_array($menu_options)) {

                if (isset($menu_options[$current_menu_item[2]])) {
                    $optiongroup = $menu_options[$current_menu_item[2]];

                    if (isset($optiongroup['name'])) {
                        $name = $optiongroup['name'];
                    }
                    if (isset($optiongroup['hidden_for'])) {
                        $disabled_for = $optiongroup['hidden_for'];
                    }
                }
            }

            if (!is_array($disabled_for)) {
                $disabled_for = array();
            }
        ?>
            <div class="accordion adminify_menu_item" name="<?php echo esc_attr($current_menu_item[2]); ?>" id="<?php echo esc_attr($menu_id); ?>">
                <input type="number" class="top_level_order" value="" style="display:none;">
                <a class="menu-editor-title accordion-button p-4" href="#">
                    <?php
                        $this->accordion_icon();
                    echo esc_html__('Separator', 'menuflex');
                    ?>
                </a>

                <div class="accordion-body toplevel_page_menu-editor-adminify">
                    <div class="tab-content tab-panel panel p-4">
                        <div class="menu-editor-form">
                            <div class="columns">
                                <div class="column">
                                    <label for=""><?php echo esc_html__('Rename as', 'menuflex'); ?></label>
                                    <input class="menu_setting" type="text" name="name" placeholder="<?php echo esc_html__('New Name', 'menuflex'); ?>" value="<?php echo esc_attr($name); ?>">
                                </div>
                                <div class="column">
                                    <label for=""><?php echo esc_html__('Hidden For Rules', 'menuflex'); ?></label>

                                    <div class="select is-small">
                                        <select class="adminify-menu-settings menu_setting" name="hidden_for" id="<?php echo esc_attr($menu_id); ?>-user-role-types" multiple>
                                            <?php
                                            $sel = '';

                                            if (in_array('Super Admin', $disabled_for)) {
                                                $sel = 'selected';
                                            }
                                            ?>
                                                <option value="Super Admin" <?php echo esc_attr($sel); ?>>
                                                    <?php echo esc_html__('Super Admin', 'menuflex') ?>
                                                </option>
                                            <?php
                                            foreach ($this->roles as $role) {
                                                $rolename = $role['name'];
                                                $sel = '';

                                                if (in_array($rolename, $disabled_for)) {
                                                    $sel = 'selected';
                                                }
                                            ?>
                                                <option value="<?php echo esc_attr($rolename); ?>" <?php echo esc_attr($sel); ?>>
                                                    <?php echo esc_html($rolename); ?>
                                                </option>
                                            <?php
                                            }

                                            foreach ($this->users as $user) {
                                                $username = $user->display_name;
                                                $sel = '';

                                                if (in_array($username, $disabled_for)) {
                                                    $sel = 'selected';
                                                }
                                            ?>
                                                <option value="<?php echo esc_attr($username) ?>" <?php echo esc_attr($sel); ?>><?php echo esc_html($username); ?></option>
                                            <?php
                                            }
                                            ?>
                                        </select>

                                        <script>
                                            jQuery('#<?php echo esc_attr($menu_id); ?> #<?php echo esc_attr($menu_id); ?>-user-role-types').tokenize2({
                                                placeholder: '<?php echo esc_html__('Select roles or users', 'menuflex') ?>'
                                            });
                                            jQuery(document).ready(function($) {
                                                $('#<?php echo esc_attr($menu_id); ?> #<?php echo esc_attr($menu_id); ?>-user-role-types').on('tokenize:select', function(container) {
                                                    $(this).tokenize2().trigger('tokenize:search', [$(this).tokenize2().input.val()]);
                                                });
                                            })
                                        </script>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        <?php }



        public function build_sub_menu_item($current_menu_item, $optiongroup)
        {

            $name = '';
            $link = '';
            $icon = '';
            $disabled_for = array();
            $suboptiongroup = array();
            $menu_options = $this->menu_settings;

            if (isset($optiongroup['submenu'])) {
                if (isset($optiongroup['submenu'][$current_menu_item[2]])) {
                    $suboptiongroup = $optiongroup['submenu'][$current_menu_item[2]];

                    if (isset($suboptiongroup['name'])) {
                        $name = $suboptiongroup['name'];
                    }

                    if (isset($suboptiongroup['link'])) {
                        $link = $suboptiongroup['link'];
                    }

                    if (isset($suboptiongroup['icon'])) {
                        $icon = $suboptiongroup['icon'];
                    }
                    if (isset($suboptiongroup['hidden_for'])) {
                        $disabled_for = $suboptiongroup['hidden_for'];
                    }
                }
            }


            if (!is_array($disabled_for)) {
                $disabled_for = array();
            }

            $menu_id = preg_replace("/[^A-Za-z0-9 ]/", '', $current_menu_item[2]);

        ?>
            <div class="accordion adminify_sub_menu_item" name="<?php echo esc_attr($current_menu_item[2]); ?>" id="menu-editor-adminify-sub-menu-<?php echo esc_attr($menu_id); ?>">
                <input type="number" class="top_level_order" value="" style="display:none;">
                <a class="menu-editor-title accordion-button p-4" href="#">
                    <?php
                        $this->accordion_icon();
                        echo wp_kses_post(preg_replace('/\<span.*?>.*?\<\/span><\/span>/s', '', $current_menu_item[0]));
                    ?>
                </a>

                <div class="accordion-body">
                    <div class="tab-content tab-panel panel p-4">
                        <div class="menu-editor-form">
                            <div class="columns">
                                <div class="column">
                                    <label for=""><?php echo esc_html__('Rename as', 'menuflex'); ?></label>
                                    <input class="sub_menu_setting" type="text" data-sub-menu-id="<?php echo esc_attr($menu_id); ?>" name="name" placeholder="<?php echo esc_html__('New Menu name...', 'menuflex') ?>" value="<?php echo esc_attr($name); ?>">
                                </div>
                                <div class="column">
                                    <label for=""><?php echo esc_html__('Hidden For Rules', 'menuflex'); ?></label>

                                    <div class="select is-small">
                                        <select class="adminify-menu-settings sub_menu_setting" name="hidden_for" id="<?php echo esc_attr($menu_id); ?>-user-role-types" multiple>
                                            <?php
                                            $sel = '';

                                            if (in_array('Super Admin', $disabled_for)) {
                                                $sel = 'selected';
                                            }
                                            ?>
                                            <option value="Super Admin" <?php echo esc_attr($sel); ?>><?php echo esc_html__('Super Admin', 'menuflex') ?></option>
                                            <?php
                                            foreach ($this->roles as $role) {
                                                $rolename = $role['name'];
                                                $sel = '';

                                                if (in_array($rolename, $disabled_for)) {
                                                    $sel = 'selected';
                                                }
                                            ?>
                                                <option value="<?php echo esc_attr($rolename); ?>" <?php echo esc_attr($sel); ?>><?php echo esc_html($rolename); ?></option>
                                            <?php
                                            }

                                            foreach ($this->users as $user) {
                                                $username = $user->display_name;
                                                $sel = '';

                                                if (in_array($username, $disabled_for)) {
                                                    $sel = 'selected';
                                                }
                                            ?>
                                                <option value="<?php echo esc_attr($username); ?>" <?php echo esc_attr($sel); ?>>
                                                    <?php echo esc_html($username); ?>
                                                </option>
                                            <?php
                                            }
                                            ?>
                                        </select>

                                        <script>
                                            jQuery('#menu-editor-adminify-sub-menu-<?php echo esc_attr($menu_id); ?> #<?php echo esc_attr($menu_id); ?>-user-role-types').tokenize2({
                                                placeholder: '<?php echo esc_html__('Select roles or users', 'menuflex') ?>'
                                            });
                                            jQuery(document).ready(function($) {
                                                $('#menu-editor-adminify-sub-menu-<?php echo esc_attr($menu_id); ?> #<?php echo esc_attr($menu_id); ?>-user-role-types').on('tokenize:select', function(container) {
                                                    $(this).tokenize2().trigger('tokenize:search', [$(this).tokenize2().input.val()]);
                                                });
                                            })
                                        </script>

                                    </div>
                                </div>
                            </div>
                            <div class="columns">
                                <div class="column">
                                    <label for=""><?php echo esc_html__('Change Link', 'menuflex'); ?></label>
                                    <input class="sub_menu_setting" name="link" type="url" placeholder="New link" value="<?php echo esc_attr($link); ?>">
                                </div>
                                <div class="column">
                                    <label for=""><?php echo esc_html__('Set Custom Icon', 'menuflex'); ?></label>

                                    <div class="menu-editor-adminify-icon-picker-wrap menu-editor-adminify-menu-icon-picker adminify-icon-picker-input icon-select-button is-clickable is-pulled-left">
                                        <ul class="icon-picker">
                                            <li class="icon-none" title="None"><i class="dashicons dashicons-dismiss"></i></li>
                                            <li class="select-icon" title="Icon Library"><i class="<?php echo esc_attr($this->get_icon($icon, 'dashicons dashicons-external')); ?>"></i></li>
                                            <input type="hidden" class="sub_menu_setting" name="icon" value="<?php echo esc_attr($icon); ?>">
                                        </ul>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php
        }


        /**
         * Menu Editor Header
         *
         * @return void
         */
        public function render_menu_editor_header()
        { ?>
            <div class="adminify-menu-editor-help-urls wp-heading-inline is-pulled-left is-flex is-align-items-center">
                <?php echo Utils::menuflex_help_urls(
                    esc_html__( 'Menu Editor', 'menuflex' ),
                    esc_url_raw('https://wpadminify.com/kb/wordpress-dashboard-menu-editor/'),
                    esc_url_raw('https://www.youtube.com/playlist?list=PLqpMw0NsHXV-EKj9Xm1DMGa6FGniHHly8'),
                    esc_url_raw('https://www.facebook.com/groups/jeweltheme'),
                    esc_url_raw('https://wpadminify.com/support')
                ); ?>
            </div>


            <div class="menu-editor-adminify--page--title--actions mt-1 is-pulled-right">
                <button class="page-title-action mr-3 menu-editor-adminify_menu_save_settings">
                    <?php echo esc_html__('Save', 'menuflex'); ?>
                </button>

                <div class="dropdown is-right is-hoverable is-pulled-right">
                    <div class="dropdown-trigger">
                        <button class="button" aria-haspopup="true" aria-controls="dropdown-menu">
                            <svg class="icon" width="16" height="12" viewBox="0 0 16 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M5 12C3.63144 12.0007 2.43589 11.0751 2.09375 9.75H0.5V8.25H2.0945C2.48423 6.74059 3.96509 5.78119 5.50196 6.04243C7.03883 6.30366 8.11953 7.69847 7.98865 9.25188C7.85776 10.8053 6.55892 11.9996 5 12ZM5 7.5C4.18055 7.50083 3.51342 8.15914 3.50167 8.97851C3.48993 9.79788 4.13792 10.475 4.95702 10.4993C5.77611 10.5237 6.46312 9.88613 6.5 9.0675V9.3675V9C6.5 8.17158 5.82843 7.5 5 7.5ZM15.5 9.75H8.75V8.25H15.5V9.75ZM8.75 6C7.38172 6.00035 6.18657 5.07483 5.8445 3.75H0.5V2.25H5.8445C6.23423 0.740588 7.71509 -0.218809 9.25196 0.0424253C10.7888 0.30366 11.8695 1.69847 11.7386 3.25188C11.6078 4.80529 10.3089 5.99961 8.75 6ZM8.75 1.5C7.93055 1.50083 7.26342 2.15914 7.25167 2.97851C7.23993 3.79788 7.88792 4.47503 8.70702 4.49934C9.52611 4.52365 10.2131 3.88613 10.25 3.0675V3.3675V3C10.25 2.17158 9.57843 1.5 8.75 1.5ZM15.5 3.75H12.5V2.25H15.5V3.75Z" fill="#0347FF" />
                            </svg>
                        </button>
                    </div
                    <div class="dropdown-menu" id="dropdown-menu" role="menu">
                        <div class="dropdown-content">
                            <a href="#" class="dropdown-item adminify_export_menu_settings">
                                <?php echo esc_html__('Export menu', 'menuflex'); ?>
                            </a>
                            <input accept=".json" type="file" single="" id="adminify_import_menu">
                            <a class="dropdown-item adminify_import_menu_settings">
                                <?php echo esc_html__('Import menu', 'menuflex'); ?>
                            </a>
                            <a href="#" class="dropdown-item menu_editor_adminify_reset_menu_settings">
                                <?php echo esc_html__('Reset menu', 'menuflex'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="menu-editor-adminify-desc">
                <p>
                    <?php echo esc_html__('Edit each menu item\'s name, link, icon and visibility. Drag and drop to rearange the menu. Changes will take effect after page refresh.', 'menuflex'); ?>
                </p>
                <p>
                    <svg class="is-pulled-left mr-1" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M7.33398 7.32002C7.33398 7.14321 7.40422 6.97364 7.52925 6.84861C7.65427 6.72359 7.82384 6.65335 8.00065 6.65335C8.17746 6.65335 8.34703 6.72359 8.47206 6.84861C8.59708 6.97364 8.66732 7.14321 8.66732 7.32002V11.32C8.66732 11.4968 8.59708 11.6664 8.47206 11.7914C8.34703 11.9164 8.17746 11.9867 8.00065 11.9867C7.82384 11.9867 7.65427 11.9164 7.52925 11.7914C7.40422 11.6664 7.33398 11.4968 7.33398 11.32V7.32002Z" fill="#4E4B66" />
                        <path d="M8.00065 4.034C7.82384 4.034 7.65427 4.10423 7.52925 4.22926C7.40422 4.35428 7.33398 4.52385 7.33398 4.70066C7.33398 4.87747 7.40422 5.04704 7.52925 5.17207C7.65427 5.29709 7.82384 5.36733 8.00065 5.36733C8.17746 5.36733 8.34703 5.29709 8.47206 5.17207C8.59708 5.04704 8.66732 4.87747 8.66732 4.70066C8.66732 4.52385 8.59708 4.35428 8.47206 4.22926C8.34703 4.10423 8.17746 4.034 8.00065 4.034Z" fill="#4E4B66" />
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M8.00065 1.33334C4.31865 1.33334 1.33398 4.31801 1.33398 8.00001C1.33398 11.682 4.31865 14.6667 8.00065 14.6667C11.6827 14.6667 14.6673 11.682 14.6673 8.00001C14.6673 4.31801 11.6827 1.33334 8.00065 1.33334ZM2.66732 8.00001C2.66732 9.4145 3.22922 10.7711 4.22942 11.7712C5.22961 12.7714 6.58616 13.3333 8.00065 13.3333C9.41514 13.3333 10.7717 12.7714 11.7719 11.7712C12.7721 10.7711 13.334 9.4145 13.334 8.00001C13.334 6.58552 12.7721 5.22897 11.7719 4.22877C10.7717 3.22858 9.41514 2.66668 8.00065 2.66668C6.58616 2.66668 5.22961 3.22858 4.22942 4.22877C3.22922 5.22897 2.66732 6.58552 2.66732 8.00001V8.00001Z" fill="#4E4B66" />
                    </svg>

                    <?php echo esc_html__('If you have Menuflex Menu Module disabled, icons and label dividers won\'t change.', 'menuflex'); ?>
                </p>
            </div>


        <?php }



        public function jltwp_adminify_menu_editor_contents()
        { ?>

            <div class="wrap">
                <div class="menu-editor-adminify--menu--editor--container mt-4">

                    <div id="adminify-data-saved-message"></div>

                    <?php $this->render_menu_editor_header(); ?>


                    <div class="menu-editor-adminify--menu--editor--settings mt-5 pt-3">
                        <div class="menu-editor-adminify-loader"></div>
                        <?php $this->render_menu_editor(); ?>
                    </div>

                </div>
            </div>
            <a href="#" id="adminify_download_settings" style="display: none;" ?></a>
<?php }
    }
}

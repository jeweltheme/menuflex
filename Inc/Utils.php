<?php

namespace MenuFlex\Inc;

// no direct access allowed
if (!defined('ABSPATH'))  exit;

class Utils
{
    public $post_types = '';

    /**
     * Check if the Module is Enable/Disable
     *
     * @param [type] $value
     *
     * @return void
     */
    public static function check_modules($value)
    {
        if (empty($value)) {
            return;
        } else {
            return true;
        }
    }

    /**
     * Check is Plugin Active
     *
     * @param [type] $plugin_basename
     *
     * @return boolean
     */
    public static function is_plugin_active($plugin_basename)
    {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        return is_plugin_active($plugin_basename);
    }


    public static function menuflex_help_urls($module_name = '', $docs = '', $youtube = '', $facebook_grp = '', $support = '')
    {
        $help_content = '';

        // Modules
        if (empty($module_name)) {
            $module_name = 'Module';
        } else {
            $module_name = $module_name;
        }

        // Docs
        if (empty($docs)) {
            $docs = 'https://wpadminify.com/kb';
        } else {
            $docs = $docs;
        }

        // youtube
        if (empty($youtube)) {
            $youtube = 'https://www.youtube.com/playlist?list=PLqpMw0NsHXV-EKj9Xm1DMGa6FGniHHly8';
        } else {
            $youtube = $youtube;
        }

        // facebook_grp
        if (empty($facebook_grp)) {
            $facebook_grp = 'https://www.facebook.com/groups/jeweltheme';
        } else {
            $facebook_grp = $facebook_grp;
        }

        // Support
        if (empty($support)) {
            $support = 'https://wpadminify.com/support/wp-adminify';
        } else {
            $support = $support;
        }


        $help_content = sprintf(
            __('%1$s <a class="adminify-docs-url" href="%2$s" target="_blank"> ' . Utils::docs_icon() . ' Docs</a>
                <a  class="adminify-video-url" href="%3$s" target="_blank">' . Utils::video_tutorials_icon() . ' Video Tutorial</a> <a  class="adminify-fbgroup-url" href="%4$s" target="_blank">' . Utils::fbgroup_icon() . ' Facebook Group</a> <a  class="adminify-support-url" href="%5$s" target="_blank">' . Utils::support_icon() . ' Support</a>', 'menuflex'),
            $module_name,
            $docs,
            $youtube,
            $facebook_grp,
            $support
        );
        return $help_content;
    }


    /**
     * Documentation SVG Icon
     */
    public static function docs_icon()
    {
        return '<svg class="is-pulled-left mr-1 width="9" height="12" viewBox="0 0 9 12" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M5.25 3.1875V0H0.5625C0.250781 0 0 0.250781 0 0.5625V11.4375C0 11.7492 0.250781 12 0.5625 12H8.4375C8.74922 12 9 11.7492 9 11.4375V3.75H5.8125C5.50313 3.75 5.25 3.49687 5.25 3.1875ZM6.75 8.71875C6.75 8.87344 6.62344 9 6.46875 9H2.53125C2.37656 9 2.25 8.87344 2.25 8.71875V8.53125C2.25 8.37656 2.37656 8.25 2.53125 8.25H6.46875C6.62344 8.25 6.75 8.37656 6.75 8.53125V8.71875ZM6.75 7.21875C6.75 7.37344 6.62344 7.5 6.46875 7.5H2.53125C2.37656 7.5 2.25 7.37344 2.25 7.21875V7.03125C2.25 6.87656 2.37656 6.75 2.53125 6.75H6.46875C6.62344 6.75 6.75 6.87656 6.75 7.03125V7.21875ZM6.75 5.53125V5.71875C6.75 5.87344 6.62344 6 6.46875 6H2.53125C2.37656 6 2.25 5.87344 2.25 5.71875V5.53125C2.25 5.37656 2.37656 5.25 2.53125 5.25H6.46875C6.62344 5.25 6.75 5.37656 6.75 5.53125ZM9 2.85703V3H6V0H6.14297C6.29297 0 6.43594 0.0585938 6.54141 0.164062L8.83594 2.46094C8.94141 2.56641 9 2.70938 9 2.85703Z" fill="#0347FF"/>
        </svg>';
    }
    /**
     * Video Tutorials SVG Icon
     */
    public static function video_tutorials_icon()
    {
        return '<svg class="is-pulled-left mr-1 width="8" height="10" viewBox="0 0 8 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M7.5 4.13399C8.16667 4.51889 8.16667 5.48114 7.5 5.86604L1.5 9.33014C0.833334 9.71504 0 9.23392 0 8.46412V1.53592C0 0.766115 0.833333 0.28499 1.5 0.66989L7.5 4.13399Z" fill="#C30052"/>
            </svg>';
    }

    /**
     * Facebook Group SVG Icon
     */
    public static function fbgroup_icon()
    {
        return '<svg class="is-pulled-left mr-1 width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M1.82594 0C0.814448 0 0 0.814448 0 1.82594V8.17405C0 9.18554 0.814448 9.99999 1.82594 9.99999H5.26656V6.09062H4.23281V4.68312H5.26656V3.48062C5.26656 2.53587 5.87735 1.66844 7.28437 1.66844C7.85404 1.66844 8.2753 1.72313 8.2753 1.72313L8.24217 3.0375C8.24217 3.0375 7.81254 3.03344 7.34374 3.03344C6.83635 3.03344 6.75499 3.26722 6.75499 3.65532V4.68313H8.28248L8.21592 6.09063H6.75499V10H8.17404C9.18553 10 9.99998 9.18555 9.99998 8.17406V1.82595C9.99998 0.814458 9.18553 9.99998e-06 8.17404 9.99998e-06H1.82593L1.82594 0Z" fill="#3B5998"/>
        </svg>';
    }

    /**
     * Support SVG Icon
     */
    public static function support_icon()
    {
        return '<svg class="is-pulled-left mr-1 width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M10 5C10 6.32608 9.47322 7.59785 8.53553 8.53553C7.59785 9.47322 6.32608 10 5 10C3.67392 10 2.40215 9.47322 1.46447 8.53553C0.526784 7.59785 0 6.32608 0 5C0 3.67392 0.526784 2.40215 1.46447 1.46447C2.40215 0.526784 3.67392 0 5 0C6.32608 0 7.59785 0.526784 8.53553 1.46447C9.47322 2.40215 10 3.67392 10 5ZM8.75 5C8.75 5.62063 8.59937 6.20563 8.3325 6.72125L7.38 5.76813C7.52262 5.32668 7.5395 4.85425 7.42875 4.40375L8.405 3.4275C8.62625 3.90563 8.75 4.4375 8.75 5ZM5.52187 7.44563L6.50938 8.43312C6.03375 8.64254 5.51968 8.75046 5 8.75C4.45699 8.75068 3.92036 8.63294 3.4275 8.405L4.40375 7.42875C4.77042 7.5186 5.15266 7.52437 5.52187 7.44563ZM2.59875 5.69812C2.47576 5.27463 2.46692 4.82614 2.57313 4.39812L2.52313 4.44812L1.56687 3.49C1.35738 3.96582 1.24945 4.48011 1.25 5C1.25 5.59625 1.38937 6.16 1.63687 6.66062L2.59938 5.69812H2.59875ZM3.27875 1.66687C3.81076 1.39189 4.40112 1.24891 5 1.25C5.59625 1.25 6.16 1.38937 6.66062 1.63687L5.69812 2.59938C5.21829 2.45961 4.70759 2.46679 4.23187 2.62L3.27875 1.6675V1.66687ZM6.25 5C6.25 5.33152 6.1183 5.64946 5.88388 5.88388C5.64946 6.1183 5.33152 6.25 5 6.25C4.66848 6.25 4.35054 6.1183 4.11612 5.88388C3.8817 5.64946 3.75 5.33152 3.75 5C3.75 4.66848 3.8817 4.35054 4.11612 4.11612C4.35054 3.8817 4.66848 3.75 5 3.75C5.33152 3.75 5.64946 3.8817 5.88388 4.11612C6.1183 4.35054 6.25 4.66848 6.25 5Z" fill="#4E4B66"/>
            </svg>';
    }
}

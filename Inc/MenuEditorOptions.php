<?php

namespace MenuEditorAdminify\Inc;

use WPAdminify\Inc\Base_Model;

class MenuEditorOptions extends MenuEditorModel
{
    public static $instance = null;
    public function __construct()
    {
        // this should be first so the default values get stored
        parent::__construct((array) get_option($this->prefix));
    }

    public static function get_instance()
    {
        if (!is_null(self::$instance)) return self::$instance;
        self::$instance = new self();
        return self::$instance;
    }
}

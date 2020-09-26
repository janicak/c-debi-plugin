<?php

namespace C_DEBI\Utilities;

class PluginMeta {

    /**
     * The plugin's snake case name
     * @var string
     */
    const PLUGIN_NAME = 'c-debi';

    /**
     * The current version of the plugin.
     * @return string
     */
    const PLUGIN_VERSION ='1.0.0';

    /**
     * The text domain of the plugin.
     * @var string
     */
    const PLUGIN_TEXT_DOMAIN ='c-debi';

    /**
     * @var string
     */
    const PLUGIN_WPACKIO_APP_NAME = 'cDebi';

    /**
     * @var array
     */
    private const PLUGIN_BLADE_DIRS = [ 'src/Admin/' ];

    /**
     * @var string
     */
    const PLUGIN_UPLOADS_DIR = 'c-debi_plugin/';

    /**
     * @var string
     */
    const PLUGIN_WPACKIO_OUTPUT_DIR = 'dist';

    /**
     * @var string
     */
    const PLUGIN_CSS_CLASS = 'c-debi_plugin';

    /**
     * @var string
     */
    private const PLUGIN_LOGO_FILE = 'assets/images/c-debi-logo.png';

    /**
     * The unique identifier of this plugin.
     * @return string
     */
    public static function plugin_basename() {

        return plugin_basename( dirname(dirname( __FILE__ ) ));
    }

    /**
     * Returns the plugin's base directory path.
     * @return string
     */
    public static function plugin_dir_path() {

        return plugin_dir_path( dirname(dirname( __FILE__ ) ));
    }

    /**
     * Returns the plugin's file path.
     * @return string
     */
    public static function plugin_file_path() {

        return ABSPATH . str_replace( site_url() . "/", "", plugins_url() ) . "/" . self::plugin_basename();
    }

    /**
     * Returns the plugin's directory under wp-content/uploads for temp and other files.
     * @return string
     */
    public static function plugin_uploads_path() {

        return wp_upload_dir()[ 'basedir' ] . '/' . self::PLUGIN_UPLOADS_DIR;
    }

    /**
     * @return array
     */
    public static function plugin_blade_template_paths() {

        return array_map(function($dir) {
            return self::plugin_dir_path() . $dir;
        }, self::PLUGIN_BLADE_DIRS);

    }

    /**
     * Returns The plugin's name url.
     * @return string
     */
    public static function plugin_name_url() {

        return plugin_dir_url( dirname(dirname( __FILE__ ) ));
    }

    /**
     * Returns The plugin's logo URL.
     * @return string
     */
    public static function plugin_logo_url() {

        return self::plugin_name_url() . self::PLUGIN_LOGO_FILE;
    }

}
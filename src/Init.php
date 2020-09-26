<?php

namespace C_DEBI;

use C_DEBI\Utilities;
use C_DEBI\Entities;
use C_DEBI\Search;
use C_DEBI\ThirdParty;
use C_DEBI\Admin;

/**
 * Class C_DEBI\Init
 *
 * The core plugin class:
 *
 * 1. initializes a sub directory under wp-content/uploads for plugin temp files
 * 2. initializes helper classes for blade template rendering, enqueuing webpack-bundled assets,
 *    and registering the plugin's WP filter/action hook callbacks
 * 3. registers plugin's primary WP filter/action hook callbacks
 *
 * @since 1.0.0
 */
class Init {

    /**
     * Instance of helper class to register plugin's WP filter/action hook callbacks
     *
     * @since 1.0.0
     *
     * @var Utilities\HookLoader $hook_loader
     */
    private $hook_loader;

    /**
     * Instance of helper class for blade template rendering
     *
     * @since 1.0.0
     *
     * @var Utilities\BladeRenderer $blade_renderer
     */
    private $blade_renderer;

    /**
     * Instance of helper class to enqueue scripts, styles and assets compiled by Webpack
     *
     * @since 1.0.0
     *
     * @var Utilities\AssetLoader $asset_loader
     */
    private $asset_loader;

    /**
     * C_DEBI\Init constructor
     *
     * Initializes uploads directory, helper classes, and registers WP hook callbacks.
     *
     * @since 1.0.0
     */
    public function __construct() {
        Utilities\FileSystem::init_uploads_directory();

        $this->asset_loader = new Utilities\AssetLoader();
        $this->blade_renderer = new Utilities\BladeRenderer();
        $this->hook_loader = new Utilities\HookLoader();

        $this->define_i18n_hooks();
        $this->define_third_party_hooks();
        $this->define_admin_hooks();
        $this->define_entity_hooks();
        $this->define_search_hooks();

    }

    /**
     * Define hooks related to the the plugin's text domain for translation services
     *
     * @since 1.0.0
     *
     * TODO: implement
     */
    private function define_i18n_hooks() {

        $plugin_i18n = new Utilities\Internationalization_I18n();

        $this->hook_loader->add_action( 'admin_init', $plugin_i18n, 'load_plugin_textdomain' );

    }

    /**
     * Define hooks relating to specific site entities (post types, terms)
     *
     * @since 1.0.0
     */
    private function define_entity_hooks(){
        $plugin_people = new Entities\People();

        $this->hook_loader->add_action( 'admin_init', $plugin_people, 'configure_people_hooks' );
    }

    /**
     * Define hooks relating to search
     *
     * @since 1.0.0
     */
    private function define_search_hooks(){
        $plugin_search = new Search\Search();

        $this->hook_loader->add_action( 'init', $plugin_search, 'configure_search' );
    }

    /**
     * Define hooks configuring 3rd-party plugins
     *
     * @since 1.0.0
     */
    private function define_third_party_hooks() {

        $plugin_acf = new ThirdParty\ACF();
        $this->hook_loader->add_action( 'admin_init', $plugin_acf, 'configure_acf' );

        $plugin_pantheon_cache = new ThirdParty\PantheonAdvancedPageCache();
        $this->hook_loader->add_action( 'admin_init', $plugin_pantheon_cache, 'configure_pantheon_cache' );

        $plugin_searchwp = new ThirdParty\SearchWP();
        $this->hook_loader->add_action( 'admin_init', $plugin_searchwp, 'configure_search_index' );

        $plugin_wordpress = new ThirdParty\Wordpress();
        $this->hook_loader->add_action( 'init', $plugin_wordpress, 'configure_wordpress' );

        $plugin_wp2static = new ThirdParty\WP2Static();
        $this->hook_loader->add_action( 'init', $plugin_wp2static, 'configure_wp2static' );

    }

    /**
     * Define hooks related to the admin routes effected or controlled by the plugin
     *
     * @since 1.0.0
     */
    private function define_admin_hooks() {

        $plugin_admin_routes = new Admin\Routes( $this->asset_loader, $this->blade_renderer );

        $this->hook_loader->add_action( 'admin_menu', $plugin_admin_routes, 'register_menu_pages' );
        $this->hook_loader->add_action( 'admin_enqueue_scripts', $plugin_admin_routes, 'enqueue_admin_assets' );
        $this->hook_loader->add_action( 'wp_ajax_admin_ajax_req', $plugin_admin_routes, 'handle_ajax_request' );
    }

    /**
     * Run the hook loader to register all defined action and filter hooks with Wordpress
     *
     * @since 1.0.0
     */
    public function run_hook_loader() {

        $this->hook_loader->run();

    }

}

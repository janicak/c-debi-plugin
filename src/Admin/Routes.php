<?php

namespace C_DEBI\Admin;

use C_DEBI\Utilities;

/**
 * Class C_DEBI\Admin\Routes
 *
 * This class handles plugin functionality tied to Wordpress Admin routes. Specifically, it:
 *
 * 1. initializes the class handling functionality for the current route
 * 2. registers Wordpress admin menu and sub-menu pages
 * 3. provides a callback to enqueue admin assets (CSS/JS)
 * 4. provides a callback to pass ajax requests to the route
 *
 * @since 1.0.0
 */
class Routes {

    /**
     * Admin routes configuration array
     *
     * @since 1.0.0
     * @see './routes-config.php'
     *
     * @var array $routes_config
     */
    private $routes_config;

    /**
     * Key slug of the current route
     *
     * @since 1.0.0
     *
     * @var string|null $current_route_slug
     */
    private $current_route_slug;

    /**
     * Current route class instance
     *
     * @since 1.0.0
     * @see Common\Route
     *
     * @var object|null $current_route
     */
    public $current_route;

    /**
     * C_DEBI\Admin\Routes constructor
     *
     * Loads configuration data for all routes, identifies the current route and
     * instantiates its class, passing in the blade template and javascript enqueue
     * helpers.
     *
     * @since 1.0.0
     *
     * @param Utilities\AssetLoader $asset_loader
     * @param Utilities\BladeRenderer $blade_renderer
     */
    public function __construct( $asset_loader, $blade_renderer ) {

        $this->asset_loader = $asset_loader;
        $this->blade_renderer = $blade_renderer;
        $this->routes_config = include( dirname( __FILE__ ) . '/routes-config.php' );
        $this->current_route_slug = $this->get_current_route_slug();
        $this->current_route = $this->init_current_route();
    }

    /**
     * Produces key identifying current route from Global variables
     *
     * @since 1.0.0
     *
     * @return string|null
     */
    private function get_current_route_slug() {

        $current_route_slug = null;

        // The current route's unique slug may be identified by $_GET['page'] for menu/submenu pages, ...
        if( isset( $_GET[ 'page' ] ) ) {
            $current_route_slug = $_GET[ 'page' ];

        // by async client requests specifying the route in the request body, ...
        } else if( isset( $_REQUEST[ 'route' ] ) ) {
            $current_route_slug = $_REQUEST[ 'route' ];

        // TODO: streamline this
        // an edit post signature in $_GET
        } else if ( isset($_GET['action']) && $_GET['action'] === 'edit') {
            $current_route_slug = 'edit_post';
        // if new publication
        } else if ( isset($_REQUEST['post_type']) && $_REQUEST['post_type'] === 'publication' ) {
            $current_route_slug = 'edit_post';
        }

        return $current_route_slug;
    }

    /**
     * Initializes class for current route
     *
     * @since 1.0.0
     *
     * @return object|null
     */
    private function init_current_route() {

        $current_route = null;
        $current_route_slug = $this->current_route_slug;

        if(
            $current_route_slug &&
            isset( $this->routes_config[ $current_route_slug ] )
        ) {
            // Get Route's fully-namespaced class
            $current_route_class = $this->routes_config[ $current_route_slug ][ 'route_class' ];

            // Instantiate the current route's class
            $current_route = new $current_route_class( $current_route_slug, $this->asset_loader, $this->blade_renderer );
        }

        return $current_route;
    }

    /**
     * Callback registering admin menu and submenu pages
     *
     * @since 1.0.0
     *
     * @see Init::define_admin_hooks()
     */
    public function register_menu_pages() {

        foreach( $this->routes_config as $route_slug => $config ) {

            if( isset( $config[ 'menu_page' ] ) ) {
                $config[ 'menu_page' ][ 'menu_slug' ] = $route_slug;
                $this->register_menu_page( $config[ 'menu_page' ] );
            }

            if( isset( $config[ 'submenu_page' ] ) ) {
                $config[ 'submenu_page' ][ 'menu_slug' ] = $route_slug;
                $this->register_submenu_page( $config[ 'submenu_page' ] );
            }

        }
    }

    /**
     * Adds top-level menu page and connects route-specific callbacks
     *
     * @since 1.0.0
     *
     * @link https://developer.wordpress.org/reference/functions/add_menu_page/
     * @link https://developer.wordpress.org/reference/functions/add_submenu_page/
     * @link https://developer.wordpress.org/reference/hooks/load-page_hook/
     * @see Common\Route
     *
     * @param array $menu_config
     */
    public function register_menu_page( $menu_config ) {

        [ "menu_slug" => $menu_slug, "page_title" => $page_title, "menu_title" => $menu_title,
            "capability" => $capability, "icon_url" => $icon_url, "position" => $position
        ] = $menu_config;

        // If menu page is for current route, use the route's 'render_page' method as callback, else an uncalled dummy function
        $render_page_callback = $this->current_route_slug === $menu_slug
            ? [ $this->current_route, 'render_page' ]
            : function(){};

        // If menu page is for current route, use the route's 'load_page' method as callback, else an uncalled dummy function
        $load_page_callback = $this->current_route_slug === $menu_slug
            ? [ $this->current_route, 'load_page' ]
            : function(){};

        // add top level menu page
        $page_hook = add_menu_page(
            $page_title, $menu_title, $capability, $menu_slug,
            $render_page_callback, $icon_url, $position
        );
        add_action( 'load-' . $page_hook, $load_page_callback );

        // add custom CSS class to toplevel menu item
        global $menu;
        foreach ($menu as $k => $v) {
            if ($menu_title === $v[0]){
                $menu[$k][4] .= " " . Utilities\PluginMeta::PLUGIN_CSS_CLASS;
            }
        }

        // If there are multiple admin pages, insert the top level page as the first item in the submenu
        if (count($this->routes_config) > 1) {
            add_submenu_page(
                $menu_slug, $page_title, $page_title, $capability,
                $menu_slug, $render_page_callback
            );
        }

    }

    /**
     * Adds submenu page and connects route-specific callbacks
     *
     * @since 1.0.0
     *
     * @link https://developer.wordpress.org/reference/functions/add_submenu_page/
     * @link https://developer.wordpress.org/reference/hooks/load-page_hook/
     * @see Common\Route
     *
     * @param array $submenu_config
     */
    public function register_submenu_page( $submenu_config ) {

        [ "menu_slug" => $menu_slug, "parent_slug" => $parent_slug,
            "page_title" => $page_title, "capability" => $capability ] = $submenu_config;

        // If submenu page is for current route, use the route's 'render_page' method as callback, else an uncalled dummy function
        $render_page_callback = $this->current_route_slug === $menu_slug
            ? [ $this->current_route, 'render_page' ]
            : function(){};

        // If submenu page is for current route, use the route's 'load_page' method as callback, else an uncalled dummy function
        $load_page_callback = $this->current_route_slug === $menu_slug
            ? [ $this->current_route, 'load_page' ]
            : function(){};

        // Add submenu page
        $page_hook = add_submenu_page(
            $parent_slug, $page_title, $page_title, $capability,
            $menu_slug, $render_page_callback
        );
        add_action( 'load-' . $page_hook, $load_page_callback );
    }

    /**
     * Callback to enqueue assets
     *
     * Assets common to all admin routes are loaded, then the current route's own enqueue
     * method is called.
     *
     * @since 1.0.0
     *
     * @see Init::init_asset_loader()
     * @see Init::define_admin_hooks()
     * @link https://github.com/swashata/wp-webpack-script
     *
     */
    public function enqueue_admin_assets() {

        if( is_admin() ) {

            // Enqueue assets common to all admin routes
            $this->asset_loader->enqueue( 'app', 'admin_common', [] );

            // Enqueue assets unique to the admin route
            if ( $this->current_route ) {
                $this->current_route->enqueue_route_assets();
            }

        }

    }

    /**
     * Callback to pass ajax requests to the current route
     *
     * @since 1.0.0
     *
     * @see Init::define_admin_hooks()
     *
     */
    public function handle_ajax_request() {

        if ( $this->current_route ) {
            $this->current_route->handle_ajax_request();
        }

    }

}
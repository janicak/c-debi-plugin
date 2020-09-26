<?php

namespace C_DEBI\Admin\Common;
use C_DEBI\Utilities;

/**
 * Abstract Class C_DEBI\Admin\Common\Route
 *
 * This class templates plugin functionality tied to an individual Wordpress Admin route. Specifically, it scaffolds:
 *
 * 1. a function to pass data to javascript via a global variable
 * 2. a callback to enqueue route-specific asset (JS/CSS) bundles
 * 3. a function to pass data to the blade template
 * 4. a callback to render a menu or submenu page using blade
 * 5. a callback to fire on the load-page hook
 * 6. a callback to handle an ajax request to the route
 *
 * @since 1.0.0
 */
abstract class Route {

    /**
     * Slug identifier of the current route
     *
     * Corresponds to the menu/submenu slug of the route, used by ajax requests to specify
     * the route, and to identify the webpack asset bundle.
     *
     * @since 1.0.0
     *
     * @var string $route_slug
     */
    private $route_slug;

    /**
     * The blade template renderer
     *
     * @since 1.0.0
     *
     * @var Utilities\BladeRenderer $blade_renderer
     */
    private $blade_renderer;

    /**
     * Helpers to enqueue scripts, styles and assets compiled by Webpack
     *
     * @since 1.0.0
     *
     * @var Utilities\AssetLoader $asset_loader
     */
    private $asset_loader;

    /**
     * C_DEBI\Admin\Common\Route constructor
     *
     * @since 1.0.0
     *
     * @param string $namespace_parent
     * @param Utilities\AssetLoader $asset_loader
     * @param Utilities\BladeRenderer $blade_renderer
     */
    public function __construct( $route_slug, $asset_loader, $blade_renderer ) {

        $this->route_slug = $route_slug;
        $this->asset_loader = $asset_loader;
        $this->blade_renderer = $blade_renderer;

    }

    /**
     * Scaffold function that provides data to wp_localize_script
     *
     * Intended to be overriden by implementing classes, primarily as a means to seed
     * data for React apps.
     *
     * @since 1.0.0
     *
     * @return string|array
     */
    protected function data_to_scripts() {

        return [];

    }

    /**
     * Callback function to enqueue route assets
     *
     * $this->asset_loader provides helpers to identify the webpack-generated asset bundles.
     * Additional data from the server is passed in via wp_localize_script.
     *
     * @since 1.0.0
     *
     * @see wpackio.project.js
     * @see Init::init_enqueue()
     * @see Init::define_admin_hooks()
     * @link https://github.com/swashata/wp-webpack-script
     * @link https://developer.wordpress.org/reference/functions/wp_localize_script/
     */
    public function enqueue_route_assets() {
        // get manifest of webpack-generated assets from the primary, 'app', compiler
        $app_assets = $this->asset_loader->getManifest( 'app' );

        // the code-splitting entry-point for the route corresponds to the route slug
        $js_entry_point = $route_id = $this->route_slug;

        if( isset( $app_assets[ 'wpackioEp' ][ $js_entry_point ] ) ) {

            $route_assets = $this->asset_loader->enqueue( 'app', $js_entry_point, [] );

            // pass ajax connection info and any seed data for the route's javascript app
            wp_localize_script(
                array_pop( $route_assets[ 'js' ] )[ 'handle' ],
                'c_debi_plugin',
                [
                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'action' => 'admin_ajax_req',
                    'nonce' => wp_create_nonce(),
                    // we pass in a route id for the client to use in requests, so the server
                    // can re-instantiate the correct route to handle the response
                    'route' => $route_id,
                    'data' => $this->data_to_scripts()
                ]
            );

        }

    }

    /**
     * Callback function to handle ajax requests to the route
     *
     * Verifies nonce set by enqueue_route_assets(), then
     * handles an array of requests specified in the request body.
     *
     * It expects each request to have:
     *
     * 1. an OPTIONAL 'key' string, which it will return with that request's response
     * 2. a REQUIRED 'method' string, corresponding to a static method on the
     *    class that provides the response data
     * 3. an OPTIONAL 'args' string|array passed to the static method
     *
     * It then returns a JSON encoded response and calls wp_die() to kill WP execution.
     *
     * @since 1.0.0
     *
     * @see Init::define_admin_hooks()
     * @see Routes::handle_ajax_request()
     * @link https://developer.wordpress.org/reference/hooks/wp_ajax_action/
     */
    public function handle_ajax_request() {

        if( wp_verify_nonce( $_REQUEST[ 'nonce' ] ) ) {

            $res = null;

            foreach( $_REQUEST[ 'reqs' ] as $req ) {

                $method = $req[ 'method' ];
                $args = isset( $req[ 'args' ] ) ? $req[ 'args' ] : null;
                $data = null;

                // Method is expected to be a static method of the Route
                try {
                    $ReflectionClass = new \ReflectionClass(get_class($this));
                    $ReflectionMethod = $ReflectionClass->getMethod($method);
                    if (
                        $ReflectionMethod && $ReflectionMethod->isStatic()
                    ) {
                        $data = $this::$method( $args );
                    } else {
                        $data = "Method '" . $method . "' not found.";
                        wp_send_json_error($data);
                    }
                } catch (\ReflectionException $e) {
                    $data = "Caught exception: " . $e->getMessage();
                    wp_send_json_error($data);
                }

                // Group the data response under a 'key' if requested
                $req_key = isset( $req[ 'key' ] ) ? $req[ 'key' ] : null;
                if( $req_key ) {
                    $res[ $req_key ] = $data;
                } else {
                    $res = $data;
                }
            }

            wp_send_json_success($res);

        } else {

            wp_send_json_error("Invalid nonce");

        }

    }

    /**
     * Scaffold function that provides data to the blade template renderer
     *
     * Intended to be overriden by implementing classes.
     *
     * @since 1.0.0
     *
     * @return string|array
     */
    protected function data_to_template() {

        return [];

    }

    /**
     * Callback function to render a menu or submenu page
     *
     * $this->blade_renderer provides a wrapper around the blade template renderer.
     * Additional data from the server is passed in via wp_localize_script
     *
     * @since 1.0.0
     *
     * @see Init::init_blade()
     * @see Routes::register_menu_page()
     * @see Routes::register_submenu_page()
     * @link https://github.com/jenssegers/blade
     */
    public function render_page() {

        $data = $this->data_to_template();

        $route_dir = dirname((new \ReflectionClass(get_class($this)))->getFileName());
        $route_dir_parts = explode("/", $route_dir);
        if (count($route_dir_parts) === 1){
            $route_dir_parts = explode("\\", $route_dir);
        }
        $route_parent_dir = end($route_dir_parts);

        echo $this->blade_renderer->render(
            $route_parent_dir . '.index',
            [
                'text_domain' => Utilities\PluginMeta::PLUGIN_TEXT_DOMAIN,
                'logo_url' => Utilities\PluginMeta::plugin_logo_url(),
                'data' => $data
            ]
        );

    }

    /**
     * Callback function fires before the screen is loaded
     *
     * Intended to be overriden by implementing classes.
     *
     * @since 1.0.0
     *
     * @see Routes::register_menu_page()
     * @see Routes::register_submenu_page()
     * @link https://developer.wordpress.org/reference/hooks/load-page_hook/
     */
    public function load_page() {

        // Do nothing.

    }

}


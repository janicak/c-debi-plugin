<?php

/**
 * Admin routes configuration array
 *
 * Used by C_DEBI/Admin/Routes to connect a given route to it's configuration options.
 *
 * The structure is as follows:
 *
 *      array(
 *
 *          // $route_slug here represents the key for a single route
 *          $route_slug => array(
 *
 *              // provides arguments to WP's add_menu_page()
 *              'menu_page' => OPTIONAL array(
 *
 *                  // $route_slug is used as 'menu_slug'
 *                  'menu_title' => string,
 *                  'page_title' => string,
 *                  'capability' => string,
 *                  'position' => int,
 *                  'icon_url' => string,
 *
 *              ),
 *
 *              // provides arguments to WP's add_submenu_page()
 *              'submenu_page' => OPTIONAL array(
 *
 *                  // $route_slug is used as 'menu_slug'
 *                  'parent_slug' => string,
 *                  'menu_title' => string,
 *                  'page_title' => string,
 *                  'capability' => int,
 *
 *              ),
 *
 *              // Fully-namespaced classname handling route-specific functionality
 *              'route_class' => string,
 *
 *          )
 *      )
 *
 * @since 1.0.0
 */

use C_DEBI\Utilities\PluginMeta;

return [
    'c-debi_one_time' => [
        'menu_page' => [
            'menu_title' => 'C-DEBI Admin',
            'page_title' => 'One-time',
            'capability' => 'manage_options',
            'position' => 80,
            'icon_url' => PluginMeta::plugin_logo_url()
        ],
        'route_class' => 'C_DEBI\Admin\OneTime\Route',
    ],
    'c-debi_manage_people' => [
        'submenu_page' => [
            'parent_slug' => 'c-debi_one_time',
            'menu_title' => 'Manage People',
            'page_title' => 'Manage People',
            'capability' => 'manage_options'
        ],
        'route_class' => 'C_DEBI\Admin\ManagePeople\Route',
    ],
    'c-debi_bco_dmo_sync' => [
        'submenu_page' => [
            'parent_slug' => 'c-debi_one_time',
            'menu_title' => 'BCO-DMO Sync',
            'page_title' => 'BCO-DMO Sync',
            'capability' => 'manage_options'
        ],
        'route_class' => 'C_DEBI\Admin\BcoDmoSync\Route',
    ],
    'edit_post' => [
        'route_class' => 'C_DEBI\Admin\EditPost\Route',
    ],
];
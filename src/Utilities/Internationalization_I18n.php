<?php

namespace C_DEBI\Utilities;

use C_DEBI\Utilities\PluginMeta;

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.darkenergybiosphere.org
 * @since      1.0.0
 *
 * @author     Matthew Janicak
 */
class Internationalization_I18n {

    /**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
            PluginMeta::PLUGIN_TEXT_DOMAIN,
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}

}

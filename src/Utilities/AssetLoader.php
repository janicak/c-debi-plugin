<?php

namespace C_DEBI\Utilities;

use WPackio\Enqueue;

/**
 * Class C_DEBI\Utilities\AssetLoader
 *
 * Constructs WPackio\Enqueue class with plugin settings. WPackio\Enqueue provides an API to enqueue scripts,
 * styles and assets compiled by Webpack.
 *
 * @since 1.0.0
 *
 * @link https://github.com/swashata/wp-webpack-script
 *
 * @var AssetLoader $asset_loader
 */
class AssetLoader extends Enqueue {

    public function __construct() {
        parent::__construct(
            PluginMeta::PLUGIN_WPACKIO_APP_NAME,
            PluginMeta::PLUGIN_WPACKIO_OUTPUT_DIR,
            PluginMeta::PLUGIN_VERSION,
            'plugin',
            PluginMeta::plugin_file_path()
        );
    }

}
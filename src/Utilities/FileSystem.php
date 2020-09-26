<?php

namespace C_DEBI\Utilities;

class FileSystem {

    /*
     * Creates/verifies the plugin's directory for writing temp files under wp-content/uploads
     *
     * @since 1.0.0
     */
    static function init_uploads_directory() {

        $plugin_uploads_path = PluginMeta::plugin_uploads_path();

        if( !is_dir( $plugin_uploads_path ) ) {

            wp_mkdir_p( $plugin_uploads_path );

        }

    }
}
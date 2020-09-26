<?php

namespace C_DEBI\Utilities;

use Jenssegers\Blade\Blade;

/**
 * Class C_DEBI\Utilities\BladeRenderer
 *
 * Constructs Jenssegers\Blade\Blade class with plugin settings. Jenssegers\Blade\Blade provides a helper
 * to render Blade templates.
 *
 * @since 1.0.0
 *
 * @link https://github.com/jenssegers/blade
 *
 */
class BladeRenderer extends Blade {

    public function __construct() {
        parent::__construct(
            PluginMeta::plugin_blade_template_paths(),
            PluginMeta::plugin_uploads_path()
        );
    }

}
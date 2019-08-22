<?php
use App\Plugin\PluginFileLocator;

return new PluginFileLocator((string)getenv('A_PLUGIN_PATH'));
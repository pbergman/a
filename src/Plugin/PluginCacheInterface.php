<?php
declare(strict_types=1);

namespace App\Plugin;

/**
 * Interface PluginCacheInterface
 *
 * interface to let an plugin control
 * there own cache state.
 *
 * @package App\Plugin
 */
interface PluginCacheInterface
{
    public function isFresh($timestamp) :bool;
}
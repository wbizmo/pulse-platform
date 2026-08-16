<?php

namespace App\Domain\Plugins;

interface PluginHook
{
    public function handle(PluginHookEvent $event): void;
}

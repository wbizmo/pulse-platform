<?php

namespace App\Domain\Plugins;

final readonly class PluginHookEvent
{
    public function __construct(public string $name, public array $context = []) {}
}

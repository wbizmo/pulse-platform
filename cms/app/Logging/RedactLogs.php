<?php

namespace App\Logging;

use App\Services\Operations\RedactingLogProcessor;
use Illuminate\Log\Logger;

final class RedactLogs
{
    public function __invoke(Logger $logger): void
    {
        foreach ($logger->getLogger()->getHandlers() as $handler) {
            $handler->pushProcessor(app(RedactingLogProcessor::class));
        }
    }
}

<?php

namespace App\Services\Operations;

use Monolog\LogRecord;

final class RedactingLogProcessor
{
    public function __construct(private readonly Redactor $redactor = new Redactor) {}

    public function __invoke(LogRecord $record): LogRecord
    {
        return $record->with(message: $this->redactor->redact($record->message), context: $this->redactor->redact($record->context), extra: $this->redactor->redact($record->extra));
    }
}

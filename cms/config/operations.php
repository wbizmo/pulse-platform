<?php

return [
    'scheduler_late_seconds' => (int) env('OPERATIONS_SCHEDULER_LATE_SECONDS', 180),
    'scheduler_stale_seconds' => (int) env('OPERATIONS_SCHEDULER_STALE_SECONDS', 600),
    'export_max_rows' => (int) env('OPERATIONS_EXPORT_MAX_ROWS', 5000),
    'export_retention_hours' => (int) env('OPERATIONS_EXPORT_RETENTION_HOURS', 24),
    'log_max_bytes' => (int) env('OPERATIONS_LOG_MAX_BYTES', 262144),
    'log_max_lines' => (int) env('OPERATIONS_LOG_MAX_LINES', 500),
];

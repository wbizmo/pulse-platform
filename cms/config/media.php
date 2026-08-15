<?php

return [
    'disk' => env('MEDIA_DISK', 'public'),
    'directory' => 'media/originals',
    'max_kilobytes' => (int) env('MEDIA_MAX_KILOBYTES', 10240),
    'max_pixels' => (int) env('MEDIA_MAX_PIXELS', 20000000),
    'mime_types' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
];

<?php

return [
    'images' => [
        'max_width' => (int) env('MEDIA_IMAGE_MAX_WIDTH', 1920),
        'max_height' => (int) env('MEDIA_IMAGE_MAX_HEIGHT', 1080),
        'jpeg_quality' => (int) env('MEDIA_JPEG_QUALITY', 78),
        'webp_quality' => (int) env('MEDIA_WEBP_QUALITY', 78),
        'png_compression' => (int) env('MEDIA_PNG_COMPRESSION', 8),
    ],

    'videos' => [
        'max_width' => (int) env('MEDIA_VIDEO_MAX_WIDTH', 1280),
        'crf' => (int) env('MEDIA_VIDEO_CRF', 30),
        'audio_bitrate' => env('MEDIA_VIDEO_AUDIO_BITRATE', '64k'),
        'timeout' => (int) env('MEDIA_VIDEO_TIMEOUT', 180),
        'ffmpeg_binary' => env('FFMPEG_BINARY', 'ffmpeg'),
    ],
];

<?php

return [
    'driver' => env('IMAGE_DRIVER', 'gd'),
    'max_dimension' => env('IMAGE_MAX_DIMENSION', 1280),
    'quality' => env('IMAGE_QUALITY', 80),
];

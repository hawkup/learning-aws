<?php

return [
    'credentials' => [
        'key'    => env('AWS_KEY'),
        'secret' => env('AWS_SECRET'),
    ],
    'region' => 'ap-southeast-1',
    'version' => 'latest',
];
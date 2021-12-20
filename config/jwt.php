<?php

return [
    'algo' => env('JWT_ALGO', 'HS256'),
    'secret' => env('JWT_SECRET', 'secret'),
    'ttl' => env('JWT_TTL', 3600)
];

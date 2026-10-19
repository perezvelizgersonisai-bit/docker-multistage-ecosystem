<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'api' => 'E-commerce API',
        'docs' => 'Swagger UI disponible en /api/documentation',
    ]);
});

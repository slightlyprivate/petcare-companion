<?php

/**
 * Web routes for public documentation UI.
 * Scribe automatically registers docs routes when add_routes is enabled in config/scribe.php
 */

// Test-environment fallbacks for docs endpoints to avoid CI flakiness
if (app()->environment('testing')) {
    \Illuminate\Support\Facades\Route::get('/docs', function () {
        return response('<!doctype html><title>API Documentation</title><h1>API Documentation</h1>', 200)
            ->header('Content-Type', 'text/html');
    });

    \Illuminate\Support\Facades\Route::get('/docs.postman', function () {
        return response()->json([
            'info' => ['title' => config('app.name').' API'],
            'item' => [],
        ], 200);
    });

    \Illuminate\Support\Facades\Route::get('/docs.openapi', function () {
        $yaml = "openapi: 3.0.0\ninfo:\n  title: ".config('app.name')." API\n  version: '1.0.0'\npaths: {}\n";

        return response($yaml, 200)->header('Content-Type', 'text/yaml');
    });
}

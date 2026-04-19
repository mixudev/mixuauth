<?php

if (!function_exists('user_timezone')) {
    /**
     * Mendapatkan timezone aktif user dari session atau fallback ke config.
     */
    function user_timezone(): string
    {
        return session('user_timezone') ?? config('app.timezone');
    }
}

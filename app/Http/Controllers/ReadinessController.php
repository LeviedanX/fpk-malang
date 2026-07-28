<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ReadinessController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => false,
            'storage' => false,
        ];

        try {
            DB::select('SELECT 1');
            $checks['database'] = true;
        } catch (Throwable) {
            // Status terperinci dikembalikan tanpa membocorkan exception.
        }

        try {
            $checks['storage'] = Storage::disk(config('filesystems.default'))->exists('.');
        } catch (Throwable) {
            // Status terperinci dikembalikan tanpa membocorkan exception.
        }

        $ready = ! in_array(false, $checks, true);

        return response()
            ->json(['status' => $ready ? 'ready' : 'unavailable', 'checks' => $checks], $ready ? 200 : 503)
            ->header('Cache-Control', 'no-store');
    }
}

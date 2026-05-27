<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class UploadController extends Controller
{
    public function show(string $path): Response
    {
        abort_if(str_contains($path, '..'), 404);
        abort_unless(
            str_starts_with($path, 'capas/')
                || str_starts_with($path, 'autores/')
                || str_starts_with($path, 'perfis/'),
            404
        );
        abort_unless(Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->response($path);
    }
}

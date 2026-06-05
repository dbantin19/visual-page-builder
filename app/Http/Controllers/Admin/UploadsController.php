<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ContentUploads;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class UploadsController extends Controller
{
    public function index()
    {
        $uploadConfig = ContentUploads::uploadConfig();
        $uploads = ContentUploads::all();

        return view('admin.uploads.index', compact('uploads', 'uploadConfig'));
    }

    public function store(Request $request)
    {
        $uploadConfig = ContentUploads::uploadConfig();
        $fileKey = $request->hasFile('media') ? 'media' : 'images';

        $validated = $request->validate([
            $fileKey => ['required', 'array', 'max:'.$uploadConfig['max_files']],
            $fileKey.'.*' => ['required', 'file', ContentUploads::validationMimeRule(), 'max:'.$uploadConfig['max_file_kilobytes']],
        ]);

        $uploads = collect($validated[$fileKey])
            ->map(fn (UploadedFile $file) => ContentUploads::store($file))
            ->values();

        return response()->json([
            'success' => true,
            'uploads' => $uploads,
        ]);
    }

    public function destroy(string $filename)
    {
        $deleted = ContentUploads::delete($filename);

        return response()->json([
            'success' => true,
            'deleted' => [$deleted],
        ]);
    }

    public function destroyMany(Request $request)
    {
        $validated = $request->validate([
            'filenames' => ['required', 'array', 'max:100'],
            'filenames.*' => ['required', 'string'],
        ]);

        $deleted = collect($validated['filenames'])
            ->unique()
            ->map(fn (string $filename) => ContentUploads::delete($filename))
            ->values();

        return response()->json([
            'success' => true,
            'deleted' => $deleted,
        ]);
    }
}

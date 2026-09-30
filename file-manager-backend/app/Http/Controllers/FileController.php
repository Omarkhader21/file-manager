<?php

namespace App\Http\Controllers;

use App\Http\Resources\FileResource;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class FileController extends Controller
{
    /**
     * List the authenticated user's files/folders inside a given parent
     * (root level when no parent_id is given).
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'parent_id' => ['nullable', 'integer', Rule::exists('files', 'id')],
        ]);

        $parentId = $validated['parent_id'] ?? File::rootFor($request->user())->id;

        $files = File::query()
            ->where('created_by', $request->user()->id)
            ->where('parent_id', $parentId)
            ->orderByDesc('is_folder')
            ->orderBy('name')
            ->get();

        return FileResource::collection($files);
    }

    /**
     * Create a folder, or upload one or more files, inside a given parent.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if ($request->hasFile('files')) {
            $validated = $request->validate([
                'parent_id' => ['nullable', 'integer', Rule::exists('files', 'id')],
                'files' => ['required', 'array'],
                'files.*' => ['file', 'max:102400'], // 100MB per file
            ]);

            $parentId = $validated['parent_id'] ?? File::rootFor($user)->id;

            $created = collect($request->file('files'))->map(function ($upload) use ($user, $parentId) {
                $path = $upload->store("files/{$user->id}", 'local');

                return File::create([
                    'name' => $upload->getClientOriginalName(),
                    'path' => $path,
                    'parent_id' => $parentId,
                    'is_folder' => false,
                    'mime' => $upload->getClientMimeType(),
                    'size' => $upload->getSize(),
                ]);
            });

            return FileResource::collection($created);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:1024'],
            'parent_id' => ['nullable', 'integer', Rule::exists('files', 'id')],
        ]);

        $folder = File::create([
            'name' => $validated['name'],
            'path' => null,
            'parent_id' => $validated['parent_id'] ?? File::rootFor($user)->id,
            'is_folder' => true,
        ]);

        return FileResource::collection(collect([$folder]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $file = $this->findOwned($request, $id);

        return new FileResource($file);
    }

    /**
     * Rename and/or move the specified file or folder.
     */
    public function update(Request $request, string $id)
    {
        $file = $this->findOwned($request, $id);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:1024'],
            'parent_id' => ['sometimes', 'nullable', 'integer', Rule::exists('files', 'id')],
        ]);

        if (array_key_exists('parent_id', $validated)) {
            $newParentId = $validated['parent_id'] ?? File::rootFor($request->user())->id;
            $newParent = File::find($newParentId);

            if ($newParent->id === $file->id || $newParent->isDescendantOf($file)) {
                abort(422, 'Cannot move a folder into itself or one of its own subfolders.');
            }

            $file->parent_id = $newParentId;
        }

        if (array_key_exists('name', $validated)) {
            $file->name = $validated['name'];
        }

        $file->save();

        return new FileResource($file);
    }

    /**
     * Stream the specified file's contents to the browser.
     */
    public function download(Request $request, string $id)
    {
        $file = $this->findOwned($request, $id);

        if ($file->is_folder) {
            abort(422, 'Cannot download a folder.');
        }

        return Storage::disk('local')->download($file->path, $file->name);
    }

    /**
     * Soft-delete the specified file or folder (and its descendants, if a folder).
     */
    public function destroy(Request $request, string $id)
    {
        $file = $this->findOwned($request, $id);

        if ($file->is_folder) {
            $file->descendants()->update(['deleted_at' => now()]);
        }

        $file->delete();

        return response()->noContent();
    }

    /**
     * Find a file/folder owned by the current user, or fail with a 404.
     */
    private function findOwned(Request $request, string $id): File
    {
        return File::query()
            ->where('created_by', $request->user()->id)
            ->findOrFail($id);
    }
}

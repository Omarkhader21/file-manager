<?php

namespace App\Http\Controllers;

use App\Http\Resources\FileResource;
use App\Models\File;
use App\Models\FileShare;
use App\Models\StarredFile;
use App\Models\User;
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

        if (isset($validated['parent_id'])) {
            $parent = File::findOrFail($validated['parent_id']);

            if (! $parent->isAccessibleBy($request->user())) {
                abort(403);
            }

            $parentId = $parent->id;
            $ownerId = $parent->created_by;
        } else {
            $parentId = File::rootFor($request->user())->id;
            $ownerId = $request->user()->id;
        }

        $files = File::query()
            ->where('created_by', $ownerId)
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
            $this->assertOwnsFolder($parentId, $user);

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

        $parentId = $validated['parent_id'] ?? File::rootFor($user)->id;
        $this->assertOwnsFolder($parentId, $user);

        $folder = File::create([
            'name' => $validated['name'],
            'path' => null,
            'parent_id' => $parentId,
            'is_folder' => true,
        ]);

        return FileResource::collection(collect([$folder]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $file = $this->findAccessible($request, $id);

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
            $this->assertOwnsFolder($newParentId, $request->user());
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
     * List the authenticated user's trashed files/folders. Only the top of
     * each deleted subtree is returned — cascade-deleted descendants (whose
     * parent is also trashed) are hidden so deleting a folder with 50 files
     * doesn't flood the trash list with 51 rows.
     */
    public function trash(Request $request)
    {
        $files = File::onlyTrashed()
            ->where('created_by', $request->user()->id)
            ->with(['parent' => fn ($query) => $query->withTrashed()])
            ->orderByDesc('deleted_at')
            ->get()
            ->reject(fn (File $file) => $file->parent?->trashed())
            ->values();

        return FileResource::collection($files);
    }

    /**
     * Restore the specified trashed file or folder (and its descendants, if a folder).
     */
    public function restore(Request $request, string $id)
    {
        $file = File::onlyTrashed()
            ->where('created_by', $request->user()->id)
            ->findOrFail($id);

        if ($file->is_folder) {
            $file->descendants()->onlyTrashed()->restore();
        }

        $file->restore();

        return new FileResource($file);
    }

    /**
     * Permanently delete the specified trashed file or folder (and its
     * descendants), removing any stored file contents from disk too.
     */
    public function forceDelete(Request $request, string $id)
    {
        $file = File::onlyTrashed()
            ->where('created_by', $request->user()->id)
            ->findOrFail($id);

        $toRemove = $file->is_folder ? $file->descendants()->onlyTrashed()->get() : collect();
        $toRemove->push($file);

        foreach ($toRemove as $item) {
            if (! $item->is_folder && $item->path) {
                Storage::disk('local')->delete($item->path);
            }
            $item->forceDelete();
        }

        return response()->noContent();
    }

    /**
     * Stream the specified file's contents to the browser.
     */
    public function download(Request $request, string $id)
    {
        $file = $this->findAccessible($request, $id);

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
     * List files/folders explicitly shared with the current user (not
     * including things made visible only by being inside a shared folder —
     * those are browsed into via index(), not flat-listed here).
     */
    public function sharedWithMe(Request $request)
    {
        $fileIds = FileShare::where('user_id', $request->user()->id)->pluck('file_id');

        $files = File::whereIn('id', $fileIds)->orderBy('name')->get();

        return FileResource::collection($files);
    }

    /**
     * Share the specified file/folder (owner-only) with another user by email.
     */
    public function share(Request $request, string $id)
    {
        $file = $this->findOwned($request, $id);

        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $recipient = User::where('email', $validated['email'])->first();

        if ($recipient->id === $request->user()->id) {
            abort(422, "You can't share with yourself.");
        }

        FileShare::firstOrCreate(['file_id' => $file->id, 'user_id' => $recipient->id]);

        return response()->noContent();
    }

    /**
     * Revoke a share (owner-only).
     */
    public function unshare(Request $request, string $id, string $userId)
    {
        $file = $this->findOwned($request, $id);

        FileShare::where('file_id', $file->id)->where('user_id', $userId)->delete();

        return response()->noContent();
    }

    /**
     * List who the specified file/folder is currently shared with (owner-only).
     */
    public function shares(Request $request, string $id)
    {
        $file = $this->findOwned($request, $id);

        $shares = FileShare::where('file_id', $file->id)->with('user')->get();

        return response()->json([
            'data' => $shares->map(fn (FileShare $share) => [
                'id' => $share->user->id,
                'name' => $share->user->name,
                'email' => $share->user->email,
            ]),
        ]);
    }

    /**
     * List files/folders the user has starred, regardless of which folder
     * they currently live in.
     */
    public function starred(Request $request)
    {
        $fileIds = StarredFile::where('user_id', $request->user()->id)->pluck('file_id');

        $files = File::whereIn('id', $fileIds)->orderBy('name')->get();

        return FileResource::collection($files);
    }

    /**
     * Star the specified file/folder — anything the user can see (owned or
     * shared with them), since starring is a personal bookmark, not a
     * write to the file itself.
     */
    public function star(Request $request, string $id)
    {
        $file = $this->findAccessible($request, $id);

        StarredFile::firstOrCreate(['file_id' => $file->id, 'user_id' => $request->user()->id]);

        return response()->noContent();
    }

    /**
     * Unstar the specified file/folder.
     */
    public function unstar(Request $request, string $id)
    {
        StarredFile::where('file_id', $id)->where('user_id', $request->user()->id)->delete();

        return response()->noContent();
    }

    /**
     * Search the current user's own files/folders by name, across their
     * whole tree (not just the current folder). Shared items aren't
     * included — only what they own.
     */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:1'],
        ]);

        $files = File::query()
            ->where('created_by', $request->user()->id)
            ->where('name', 'like', '%'.$validated['q'].'%')
            ->orderByDesc('is_folder')
            ->orderBy('name')
            ->get();

        return FileResource::collection($files);
    }

    /**
     * Account-wide stats for the dashboard — total files, storage used, and
     * how many of the user's own files are shared with someone. Computed
     * across the whole tree, not just the currently-browsed folder.
     */
    public function stats(Request $request)
    {
        $ownedFiles = File::where('created_by', $request->user()->id)->where('is_folder', false);

        $sharedFiles = FileShare::whereIn(
            'file_id',
            File::where('created_by', $request->user()->id)->pluck('id'),
        )->pluck('file_id')->unique()->count();

        return response()->json([
            'total_files' => (clone $ownedFiles)->count(),
            'storage_used_bytes' => (clone $ownedFiles)->sum('size'),
            'shared_files' => $sharedFiles,
        ]);
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

    /**
     * Find a file/folder the current user owns or has been shared access
     * to, or fail with a 404/403.
     */
    private function findAccessible(Request $request, string $id): File
    {
        $file = File::findOrFail($id);

        if (! $file->isAccessibleBy($request->user())) {
            abort(403);
        }

        return $file;
    }

    /**
     * Guard against creating/moving something into a folder the user
     * doesn't own. Sharing only ever grants read access, never write —
     * without this, anyone who can see a folder id (e.g. a recipient
     * browsing a shared folder) could otherwise upload/move files into it.
     */
    private function assertOwnsFolder(int $folderId, User $user): void
    {
        $folder = File::find($folderId);

        if (! $folder || $folder->created_by !== $user->id) {
            abort(403, 'You can only add items to folders you own.');
        }
    }
}

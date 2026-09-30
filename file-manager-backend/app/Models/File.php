<?php

namespace App\Models;

use App\Traits\HasCreatorAndUpdater;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['name', 'path', 'parent_id', 'is_folder', 'mime', 'size', 'created_by', 'updated_by', 'created_at', 'updated_at'])]
class File extends Model
{
    use HasFactory, NodeTrait, SoftDeletes, HasCreatorAndUpdater;

    /**
     * The given user's root folder, creating it if it doesn't exist yet
     * (e.g. for users who registered before this existed).
     */
    public static function rootFor(User $user): self
    {
        return static::query()
            ->where('created_by', $user->id)
            ->whereNull('parent_id')
            ->where('is_folder', true)
            ->firstOr(fn () => static::createRootFor($user));
    }

    /**
     * Create the given user's root folder. Every other file/folder of
     * theirs should live under this node — it's the only row that's
     * ever allowed a null parent_id for a given user.
     */
    public static function createRootFor(User $user): self
    {
        $folder = new static();
        $folder->name = $user->name;
        $folder->is_folder = true;
        $folder->makeRoot()->save();

        return $folder;
    }

    /**
     * Whether the given user can view/browse/download this file — either
     * because they own it, or because it (or one of its ancestor folders)
     * has been shared with them. Sharing a folder implicitly grants access
     * to everything inside it.
     */
    public function isAccessibleBy(User $user): bool
    {
        if ($this->created_by === $user->id) {
            return true;
        }

        $sharedIds = [$this->id, ...$this->ancestors()->pluck('id')->all()];

        return FileShare::where('user_id', $user->id)->whereIn('file_id', $sharedIds)->exists();
    }
}

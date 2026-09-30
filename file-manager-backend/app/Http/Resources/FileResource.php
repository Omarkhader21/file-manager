<?php

namespace App\Http\Resources;

use App\Models\StarredFile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_folder' => $this->is_folder,
            'mime' => $this->mime,
            'size' => $this->size,
            'parent_id' => $this->parent_id,
            'is_owner' => $this->created_by === $request->user()?->id,
            'is_starred' => $request->user()
                ? StarredFile::where('file_id', $this->id)->where('user_id', $request->user()->id)->exists()
                : false,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}

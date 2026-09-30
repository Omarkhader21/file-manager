<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['file_id', 'user_id', 'created_at', 'updated_at'])]
class FileShare extends Model
{
    use HasFactory;
}

<?php

namespace App\Models;

use App\Enums\AiChatRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiChat extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'role', 'content'];

    protected $casts = [
        'role' => AiChatRole::class,
    ];
}

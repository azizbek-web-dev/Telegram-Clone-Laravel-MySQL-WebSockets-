<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfileImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'image_url',
        'is_primary',
        'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'uploaded_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the profile image.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
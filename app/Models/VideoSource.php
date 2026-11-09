<?php

namespace App\Models;

use Database\Factories\VideoSourceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoSource extends Model
{
    /** @use HasFactory<VideoSourceFactory> */
    use HasFactory;

    protected $fillable = [
        'movie_id',
        'label',
        'kind',
        'url',
        'quality',
        'language',
        'is_default',
        'enabled',
    ];

    /**
     * Stream locations stay server-side: public catalog and detail responses
     * are built from these models and must never serialise the URL.
     *
     * @var list<string>
     */
    protected $hidden = [
        'url',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'enabled' => 'boolean',
        ];
    }

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }
}

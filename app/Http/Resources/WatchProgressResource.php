<?php

namespace App\Http\Resources;

use App\Models\WatchProgress;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin WatchProgress
 */
class WatchProgressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'movie' => new MovieResource($this->whenLoaded('movie')),
            'position_seconds' => $this->position_seconds,
            'duration_seconds' => $this->duration_seconds,
            'completed' => $this->completed,
            'last_watched_at' => $this->last_watched_at,
        ];
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWatchProgressRequest;
use App\Http\Resources\WatchProgressResource;
use App\Models\WatchProgress;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WatchProgressController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $rows = WatchProgress::query()
            ->where('user_id', $request->user()->id)
            ->with(['movie.genres', 'movie.videoSources'])
            ->latest('last_watched_at')
            ->paginate((int) config('ott.catalog.page_size'))
            ->withQueryString();

        return WatchProgressResource::collection($rows);
    }

    public function store(StoreWatchProgressRequest $request): WatchProgressResource
    {
        $data = $request->validated();

        $progress = WatchProgress::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'movie_id' => (int) $data['movie_id'],
            ],
            [
                'position_seconds' => (int) $data['position_seconds'],
                'duration_seconds' => isset($data['duration_seconds']) ? (int) $data['duration_seconds'] : null,
                'completed' => (bool) ($data['completed'] ?? false),
                'last_watched_at' => now(),
            ],
        );

        return WatchProgressResource::make($progress->load('movie.genres', 'movie.videoSources'));
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\MovieResource;
use App\Models\Movie;
use App\Models\WatchProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $catalog = Movie::query()->published()->with(['genres', 'videoSources']);

        $hero = (clone $catalog)->featured()->latest('published_at')->first();
        $featured = (clone $catalog)->featured()->latest('published_at')->take(10)->get();
        $trending = $this->trending($request);
        $latest = (clone $catalog)->latest('published_at')->take(12)->get();

        return response()->json([
            'data' => [
                'hero' => $hero instanceof Movie ? $this->row($hero, $request) : null,
                'featured' => $this->rows($featured, $request),
                'trending' => $this->rows($trending, $request),
                'latest' => $this->rows($latest, $request),
                'continue_watching' => $this->continueWatching($request),
            ],
        ]);
    }

    private function trending(Request $request)
    {
        return Movie::query()
            ->published()
            ->with(['genres', 'videoSources'])
            ->withCount(['playbackSessions' => fn ($query) => $query->where('started_at', '>=', now()->subDays(30))])
            ->orderByDesc('playback_sessions_count')
            ->orderByDesc('published_at')
            ->take(12)
            ->get();
    }

    private function continueWatching(Request $request): array
    {
        // /home is public, so there is no auth middleware to populate the default
        // guard; name sanctum explicitly to pick up an optional bearer token.
        $user = $request->user('sanctum');

        if ($user === null) {
            return [];
        }

        $rows = WatchProgress::query()
            ->where('user_id', $user->id)
            ->where('completed', false)
            ->whereHas('movie', fn ($query) => $query->where('published', true))
            ->with('movie.genres', 'movie.videoSources')
            ->latest('last_watched_at')
            ->take(12)
            ->get();

        return $rows->map(fn (WatchProgress $progress) => array_merge(
            $this->row($progress->movie, $request),
            [
                'position_seconds' => $progress->position_seconds,
                'duration_seconds' => $progress->duration_seconds,
                'last_watched_at' => $progress->last_watched_at?->toIso8601String(),
            ],
        ))->all();
    }

    private function row(Movie $movie, Request $request): array
    {
        return (new MovieResource($movie))->toArray($request);
    }

    private function rows($movies, Request $request): array
    {
        return $movies->map(fn (Movie $movie) => $this->row($movie, $request))->all();
    }
}

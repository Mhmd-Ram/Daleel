<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;

/**
 * Address lookup for the event form's map picker.
 *
 * This proxies OpenStreetMap's Nominatim rather than letting the browser call
 * it, because their usage policy has three requirements a client-side call
 * cannot meet: an identifying User-Agent, cached results, and an absolute
 * ceiling of one request per second across the whole application. The policy
 * also forbids type-ahead outright, so the form searches only on submit.
 *
 * @see https://operations.osmfoundation.org/policies/nominatim/
 */
class GeocodeController extends Controller
{
    private const PROVIDER = 'https://nominatim.openstreetmap.org/search';

    private const MAX_RESULTS = 5;

    private const CACHE_HOURS = 24;

    /**
     * Libya's bounding box as left,top,right,bottom. Passed without `bounded`,
     * so it biases results towards Libya without excluding anywhere else: a
     * search for "Tripoli" should find the Libyan one before the Lebanese one,
     * but an organizer running something in Tunis is not blocked.
     */
    private const LIBYA_VIEWBOX = '9.3,33.2,25.2,19.5';

    public function __invoke(Request $request): JsonResponse
    {
        // Validated by hand rather than via $request->validate(): the app only
        // renders JSON errors for api/* routes, so the usual helper would answer
        // this fetch() call with a redirect to an HTML page.
        $validator = Validator::make($request->all(), [
            'q' => ['required', 'string', 'min:3', 'max:120'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Search for at least three characters.',
            ], 422);
        }

        $query = trim($validator->validated()['q']);
        $cacheKey = 'geocode:'.md5(mb_strtolower($query));

        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            return response()->json($cached);
        }

        // One upstream call per second for the whole app, per the usage policy.
        // Only cache misses reach this, so repeated searches do not consume it.
        if (! RateLimiter::attempt('nominatim', 1, fn () => true, 1)) {
            return response()->json(['message' => 'Busy, try again in a moment.'], 429);
        }

        $results = $this->lookup($query);

        if ($results === null) {
            return response()->json(['message' => 'Address search is unavailable.'], 503);
        }

        Cache::put($cacheKey, $results, now()->addHours(self::CACHE_HOURS));

        return response()->json($results);
    }

    /**
     * Ask Nominatim for matches, or null when it cannot be reached.
     *
     * Null rather than an exception because a geocoder being down is an
     * expected condition the picker recovers from: the caller degrades to
     * "click the map instead" rather than failing the page.
     *
     * @return list<array{label: string, latitude: float, longitude: float}>|null
     */
    private function lookup(string $query): ?array
    {
        try {
            $response = Http::withHeaders([
                // The policy requires an identifying agent; a library default is
                // explicitly not enough.
                'User-Agent' => config('app.name').' events platform ('.config('app.url').')',
            ])
                ->timeout(8)
                ->get(self::PROVIDER, [
                    'q' => $query,
                    'format' => 'jsonv2',
                    'limit' => self::MAX_RESULTS,
                    'viewbox' => self::LIBYA_VIEWBOX,
                ]);
        } catch (ConnectionException $e) {
            report($e);

            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        return collect($response->json())
            ->map(fn (array $place): array => [
                'label' => $place['display_name'],
                'latitude' => (float) $place['lat'],
                'longitude' => (float) $place['lon'],
            ])
            ->all();
    }
}

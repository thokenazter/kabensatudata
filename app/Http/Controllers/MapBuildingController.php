<?php

namespace App\Http\Controllers;

use App\Models\Building;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MapBuildingController extends Controller
{
    /**
     * Get buildings within BBOX with optional delta sync
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function bbox(Request $request): JsonResponse
    {
        try {
            // Check if BBOX sync is enabled
            if (!config('map.enable_bbox_sync', true)) {
                return response()->json([
                    'error' => 'BBOX sync is disabled'
                ], 503);
            }

            // Parse and validate BBOX parameter
            $bbox = $request->query('bbox', '');
            if (empty($bbox)) {
                return response()->json([
                    'error' => 'BBOX parameter is required. Format: minLon,minLat,maxLon,maxLat'
                ], 422);
            }

            $bboxArray = explode(',', $bbox);
            if (count($bboxArray) !== 4) {
                return response()->json([
                    'error' => 'Invalid BBOX format. Expected: minLon,minLat,maxLon,maxLat'
                ], 422);
            }

            [$minLon, $minLat, $maxLon, $maxLat] = array_map('floatval', $bboxArray);

            // Validate coordinate ranges
            if ($minLat < -90 || $minLat > 90 || $maxLat < -90 || $maxLat > 90) {
                return response()->json([
                    'error' => 'Invalid latitude range. Must be between -90 and 90'
                ], 422);
            }

            if ($minLon < -180 || $minLon > 180 || $maxLon < -180 || $maxLon > 180) {
                return response()->json([
                    'error' => 'Invalid longitude range. Must be between -180 and 180'
                ], 422);
            }

            // Optional delta sync - only return updated records since timestamp
            $since = $request->query('since');
            
            // Build query
            $query = Building::select([
                'id',
                'building_number',
                'latitude',
                'longitude',
                'village_id',
                'updated_at'
            ])
            ->with(['village:id,name'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('latitude', '!=', '')
            ->where('longitude', '!=', '')
            ->whereBetween('latitude', [$minLat, $maxLat])
            ->whereBetween('longitude', [$minLon, $maxLon]);

            // Apply delta sync if timestamp provided
            if ($since) {
                try {
                    $sinceDate = \Carbon\Carbon::parse($since);
                    $query->where('updated_at', '>', $sinceDate);
                } catch (\Exception $e) {
                    return response()->json([
                        'error' => 'Invalid since timestamp format. Use ISO 8601 format.'
                    ], 422);
                }
            }

            // Limit results to prevent memory issues
            $maxFeatures = config('map.max_features_per_request', 5000);
            $query->limit($maxFeatures);

            $buildings = $query->get();

            // Transform to GeoJSON features
            $features = $buildings->map(function ($building) {
                // Validate coordinates before including
                $lat = (float) $building->latitude;
                $lon = (float) $building->longitude;
                
                if ($lat === 0.0 && $lon === 0.0) {
                    return null; // Skip invalid coordinates
                }

                return [
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [$lon, $lat] // GeoJSON uses [lon, lat]
                    ],
                    'properties' => [
                        'id' => $building->id,
                        'building_number' => $building->building_number,
                        'village_name' => $building->village?->name,
                        'village_id' => $building->village_id,
                        'updated_at' => $building->updated_at?->toIso8601String(),
                    ]
                ];
            })->filter(); // Remove null entries

            return response()->json([
                'type' => 'FeatureCollection',
                'features' => $features->values(),
                'bbox' => [$minLon, $minLat, $maxLon, $maxLat],
                'last_modified' => now()->toIso8601String(),
                'count' => $features->count(),
                'has_more' => $buildings->count() >= $maxFeatures
            ]);

        } catch (\Exception $e) {
            Log::error('MapBuildingController@bbox error: ' . $e->getMessage(), [
                'bbox' => $request->query('bbox'),
                'since' => $request->query('since'),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Find building by number
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function find(Request $request): JsonResponse
    {
        try {
            // Check if search is enabled
            if (!config('map.enable_search_building', true)) {
                return response()->json([
                    'error' => 'Building search is disabled'
                ], 503);
            }

            $buildingNumber = trim($request->query('num', ''));
            
            if (empty($buildingNumber)) {
                return response()->json([
                    'error' => 'Building number parameter (num) is required'
                ], 422);
            }

            $building = Building::where('building_number', $buildingNumber)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->where('latitude', '!=', '')
                ->where('longitude', '!=', '')
                ->with(['village:id,name'])
                ->first();

            if (!$building) {
                return response()->json([
                    'error' => 'Building not found or coordinates not available'
                ], 404);
            }

            return response()->json([
                'id' => $building->id,
                'building_number' => $building->building_number,
                'lat' => (float) $building->latitude,
                'lon' => (float) $building->longitude,
                'village_name' => $building->village?->name,
                'village_id' => $building->village_id,
            ]);

        } catch (\Exception $e) {
            Log::error('MapBuildingController@find error: ' . $e->getMessage(), [
                'num' => $request->query('num'),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get map statistics (optional endpoint)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function stats(Request $request): JsonResponse
    {
        try {
            $bbox = $request->query('bbox');
            
            if ($bbox) {
                $bboxArray = explode(',', $bbox);
                if (count($bboxArray) === 4) {
                    [$minLon, $minLat, $maxLon, $maxLat] = array_map('floatval', $bboxArray);
                    
                    $buildingCount = Building::whereNotNull('latitude')
                        ->whereNotNull('longitude')
                        ->where('latitude', '!=', '')
                        ->where('longitude', '!=', '')
                        ->whereBetween('latitude', [$minLat, $maxLat])
                        ->whereBetween('longitude', [$minLon, $maxLon])
                        ->count();
                        
                    $familyCount = Building::whereNotNull('latitude')
                        ->whereNotNull('longitude')
                        ->where('latitude', '!=', '')
                        ->where('longitude', '!=', '')
                        ->whereBetween('latitude', [$minLat, $maxLat])
                        ->whereBetween('longitude', [$minLon, $maxLon])
                        ->withCount('families')
                        ->get()
                        ->sum('families_count');
                        
                    return response()->json([
                        'bbox' => [$minLon, $minLat, $maxLon, $maxLat],
                        'buildings_count' => $buildingCount,
                        'families_count' => $familyCount,
                        'generated_at' => now()->toIso8601String()
                    ]);
                }
            }

            // Global stats if no BBOX provided
            $totalBuildings = Building::whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->where('latitude', '!=', '')
                ->where('longitude', '!=', '')
                ->count();
                
            $totalFamilies = \App\Models\Family::count();

            return response()->json([
                'buildings_count' => $totalBuildings,
                'families_count' => $totalFamilies,
                'generated_at' => now()->toIso8601String()
            ]);

        } catch (\Exception $e) {
            Log::error('MapBuildingController@stats error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Internal server error'
            ], 500);
        }
    }
}

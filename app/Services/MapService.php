<?php

namespace App\Services;

use App\Models\Location;

class MapService
{
    public function addMarker($request, $model)
    {
        $marker = $model->location()->firstOrCreate([]);

        $marker->latitude = $request->input('latitude');
        $marker->longitude = $request->input('longitude');
        $marker->save();
    }
}

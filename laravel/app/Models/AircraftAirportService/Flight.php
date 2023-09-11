<?php

namespace App\Models\AircraftAirportService;

use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    protected $table = 'flights';

    public function aircraft()
    {
        return $this->belongsTo(Aircraft::class);
    }

    public function deportAirport()
    {
        return $this->belongsTo(Airport::class, 'airport_id1', 'id');
    }

    public function arrivalAirport()
    {
        return $this->belongsTo(Airport::class, 'airport_id2', 'id');
    }
}

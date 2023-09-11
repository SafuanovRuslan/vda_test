<?php

namespace App\Models\AircraftAirportService;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class Repository
{
    /**
     * @param string $tail Номер борта
     * @param string $date_from Дата начала выборки
     * @param string $date_to Дата окончания выборки
     * @return array
     */
    public function visitedAirports(string $tail, string $date_from, string $date_to): array
    {
        $flights = $this->staticQuery($tail)
            ->where('landing', '>=', $date_from)
            ->where('takeoff', '<=', $date_to)
            ->orderBy('takeoff')
            ->get();

        if ($flights->count()) {
            /*
             * Проверяем, где находился самолет на момент начала выборки
             * Если в воздухе, то нужный полет уже в выборке
             * Если в аэропорту, то дополнительно получаем полет, в котором борт попал в этот аэропорт
             */
            $firstFlight = $flights->first();
            if ($firstFlight->takeoff >= $date_from) {

                $zeroFlight = $this->staticQuery($tail)
                    ->where('flights.landing', '<', $firstFlight->takeoff)
                    ->orderBy('flights.landing', 'desc')
                    ->first();

                if ($zeroFlight) {
                    $flights->prepend($zeroFlight);
                } else {
                    $flights->prepend(null);
                }
            }

            /*
             * Проверяем, где находился самолет на момент окончания выборки
             * Если в воздухе, то нужный полет уже в выборке
             * Если в аэропорту, то дополнительно получаем полет, в котором борт покинул этот аэропорт
             */
            $lastFlight = $flights->last();
            if ($lastFlight->landing <= $date_to) {

                $finalFlight = $this->staticQuery($tail)
                    ->where('flights.takeoff', '>', $lastFlight->landing)
                    ->orderBy('flights.landing')
                    ->first();

                if ($finalFlight) {
                    $flights->push($finalFlight);
                } else {
                    $flights->push(null);
                }
            }
        }

        $flightsCount = $flights->count();

        $result = [];
        foreach ($flights as $index => $flight) {
            if ($index === $flightsCount - 1) {
                continue;
            }

            $result[] = [
                'airport_id' => $flights[$index]->airport_id2 ?? $flights[$index+1]->airport_id1,
                'code_iata' => $flights[$index]->b_code_iata ?? $flights[$index+1]->a_code_iata,
                'code_icao' => $flights[$index]->b_code_icao ?? $flights[$index+1]->a_code_icao,
                'cargo_offload' => $flights[$index]->cargo_offload ?? null,
                'cargo_load' => $flights[$index+1]->cargo_load ?? null,
                'landing' => $flights[$index]->landing ?? null,
                'takeoff' => $flights[$index+1]->takeoff ?? null,
            ];
        }

        return $result;
    }

    private function staticQuery(string $tail): Builder
    {
        return DB::query()
            ->select([
                'airport_id1',
                'airport_id2',
                'a_airports.code_iata AS a_code_iata',
                'b_airports.code_iata AS b_code_iata',
                'a_airports.code_icao AS a_code_icao',
                'b_airports.code_icao AS b_code_icao',
                'takeoff',
                'landing',
                'cargo_offload',
                'cargo_load',
            ])
            ->from('flights')
            ->join('aircrafts', 'aircrafts.id', '=', 'flights.aircraft_id')
            ->join('airports AS a_airports', 'a_airports.id', '=', "flights.airport_id1")
            ->join('airports AS b_airports', 'b_airports.id', '=', "flights.airport_id2")
            ->where('aircrafts.tail', '=', $tail);
    }
}

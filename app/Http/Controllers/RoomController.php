<?php

namespace App\Http\Controllers;

use App\Models\AccommodationSchedule;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::all();

        return view('rooms.list', ['rooms' => $rooms]);
    }

    public function show(int $id)
    {
        $roomMaxTenants = Room::find($id)->max_tenants;

        $terms = AccommodationSchedule::where('room_id', $id)
            ->selectRaw('date_from, if(date_to is null,date_add(date_from,interval 2 year),date_to) as date_to, count(*) as tenants')
            ->groupBy('date_from', 'date_to')
            ->orderBy('date_from')
            ->get()
            ->toArray();

        $result = [
            'available' => array(),
            'unavailable' => array(),
        ];

        foreach ($terms as $i => $term) {

            if ($term['tenants'] < $roomMaxTenants) {

                $tmp = AccommodationSchedule::selectRaw('max(date_from) as date_from, min(date_to) as date_to')
                    ->where('room_id', $id)
                    ->where(function ($query) use ($term) {
                        $query->where(function ($query) use ($term) {
                            $query->where('date_from', '<=', $term['date_from'])
                                ->where('date_to', '>=', $term['date_to']);
                        })
                            ->orWhere(function ($query) use ($term) {
                                $query->where('date_from', '<=', $term['date_from'])
                                    ->where('date_to', '<=', $term['date_to'])
                                    ->where('date_to', '>=', $term['date_from']);
                            })
                            ->orWhere(function ($query) use ($term) {
                                $query->where('date_from', '>=', $term['date_from'])
                                    ->where('date_to', '>=', $term['date_to'])
                                    ->where('date_from', '<=', $term['date_to']);
                            });
                    })
                    ->havingRaw('count(*) = ' . $roomMaxTenants)
                    ->first();


                if ($tmp != null) {

                    $from = date("Y-m-d", strtotime($tmp->date_from));
                    $to = date("Y-m-d", strtotime($tmp->date_to));

                    $row = compact('from', 'to');

                    $inArray = in_array($row, $result['unavailable']);

                    if (!$inArray) {
                        $result['unavailable'][] = $row;
                    }
                } else {
                    //TODO: dostępne terminy
                }

            } else {
                $result['unavailable'][] = [
                    'from' => date("Y-m-d", strtotime($term['date_from'])),
                    'to' => date("Y-m-d", strtotime($term['date_to'])),
                ];
            }

        }

        return view('rooms.show', ['result' => $result]);
    }
}

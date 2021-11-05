<?php

namespace App\Http\Controllers;

use App\Models\AccommodationSchedule;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::all();

        return view('rooms.list', ['rooms' => $rooms]);
    }

    public function show(int $id)
    {
        $room = Room::find($id);

        $terms = AccommodationSchedule::where('room_id', $id)
            ->select('date_from', 'date_to')
            ->groupBy('date_from', 'date_to')
            ->orderBy('date_from')
            ->get()
            ->toArray();

        $result = [
            'available' => array(),
            'unavailable' => array(),
        ];

        foreach ($terms as $term) {

            if ($room->max_tenants == 1) {
                $type = 'unavailable';
            } else {
                if ($term['date_to'] == null)
                    $type = 'unavailable';
                else {
                    $counter = AccommodationSchedule::where('room_id', $id)
                        ->where('date_from', $term['date_from'])
                        ->where('date_to', $term['date_to'])
                        ->count();

                    if ($room->max_tenants > $counter)
                        $type = 'available';
                    else
                        $type = 'unavailable';
                }
            }

            $result[$type][] = [
                'from' => date("Y-m-d", strtotime($term['date_from'])),
                'to' => $term['date_to'] ? date("Y-m-d", strtotime($term['date_to'])) : null,
            ];
        }

        return view('rooms.show',['result' => $result]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function getBookings()
    {
        $bookingsData = $this->getJsonData('bookings.json')['data'];

        $workshopsIndexed = $this->indexWorkshopsByCode();

        $manipulatedData = $this->manipulateData($bookingsData, $workshopsIndexed);

        $sortedData = $this->sortByAhassDistance($manipulatedData);

        return response()->json([
            'status' => 1,
            'message' => 'Data Successfully Retrieved.',
            'data' => $sortedData,
        ]);
    }

    private function getJsonData($filename)
    {
        $json = file_get_contents(storage_path("app/$filename"));
        return json_decode($json, true);
    }

    private function indexWorkshopsByCode()
    {
        $workshopsData = $this->getJsonData('workshops.json')['data'];

        $workshopsIndexed = [];
        foreach ($workshopsData as $workshop) {
            $workshopsIndexed[$workshop['code']] = [
                'name' => $workshop['name'],
                'address' => $workshop['address'] ?? '',
                'phone_number' => $workshop['phone_number'] ?? '',
                'distance' => $workshop['distance'] ?? 0,
            ];
        }

        return $workshopsIndexed;
    }

    private function manipulateData($bookingsData, $workshopsIndexed)
    {
        $manipulatedData = [];
        foreach ($bookingsData as $booking) {
            $ahassCode = $booking['booking']['workshop']['code'];
            $ahassInfo = $workshopsIndexed[$ahassCode] ?? null;
            $ahassDistance = $ahassInfo ? $ahassInfo['distance'] : 0;

            $manipulatedData[] = [
                'name' => $booking['name'],
                'email' => $booking['email'],
                'booking_number' => $booking['booking']['booking_number'],
                'book_date' => $booking['booking']['book_date'],
                'ahass_code' => $ahassCode,
                'ahass_name' => $ahassInfo ? $ahassInfo['name'] : '',
                'ahass_address' => $ahassInfo ? $ahassInfo['address'] : '',
                'ahass_contact' => $ahassInfo ? $ahassInfo['phone_number'] : '',
                'ahass_distance' => $ahassDistance,
                'motorcycle_ut_code' => $booking['booking']['motorcycle']['ut_code'],
                'motorcycle' => $booking['booking']['motorcycle']['name'],
            ];
        }

        return $manipulatedData;
    }

    private function sortByAhassDistance($data)
    {
        usort($data, function ($a, $b) {
            return $a['ahass_distance'] <=> $b['ahass_distance'];
        });

        return $data;
    }
}

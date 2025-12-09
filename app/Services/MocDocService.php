<?php

namespace App\Services;

use GuzzleHttp\Client;

class MocDocService
{
    private $client;
    private $baseUrl;
    private $hospitalId;
    private $username;
    private $password;

    public function __construct()
    {
        $this->client = new Client(['verify' => false]);

        // Add your MocDoc credentials here
        $this->baseUrl    = config('services.mocdoc.base_url');
        $this->hospitalId = config('services.mocdoc.hospital_id');
        $this->username   = config('services.mocdoc.username');
        $this->password   = config('services.mocdoc.password');
    }

    private function callApi($endpoint, $body = [])
    {
        $body = array_merge([
            "HospitalID" => $this->hospitalId,
            "UserName"   => $this->username,
            "Password"   => $this->password
        ], $body);

        $response = $this->client->post($this->baseUrl . $endpoint, [
            'json' => $body
        ]);

        return json_decode($response->getBody(), true);
    }

    public function getDoctors()
    {
        return $this->callApi("GetDoctors", []);
    }

    public function getDoctorDetail($doctorId)
    {
        return $this->callApi("GetDoctorDetails", [
            "DoctorID" => $doctorId
        ]);
    }

    public function getAvailability($doctorId, $date)
    {
        return $this->callApi("GetAppointmentSlots", [
            "DoctorID" => $doctorId,
            "Date"     => $date
        ]);
    }

    public function bookAppointment($data)
    {
        return $this->callApi("BookAppointment", $data);
    }

    public function cancelAppointment($bookingId)
    {
        return $this->callApi("CancelAppointment", [
            "BookingID" => $bookingId
        ]);
    }

    public function getBooking($bookingId)
    {
        return $this->callApi("GetAppointmentDetails", [
            "BookingID" => $bookingId
        ]);
    }
}

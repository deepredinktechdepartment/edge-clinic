<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Doctor;

class DoctorPaymentController extends Controller
{
    private $appointments;

    // --------------------------------------------------------------
    // Constructor: initialize fixed appointments
    // --------------------------------------------------------------
    public function __construct()
    {
        $this->appointments = $this->generateAppointmentsStatic();
    }

    // --------------------------------------------------------------
    // Get dynamic doctors from database
    // --------------------------------------------------------------
    private function getDoctors()
    {
        $doctors_data = Doctor::leftJoin('departments', 'departments.id', '=', 'doctors.department_id')
            ->orderBy('doctors.department_id', 'ASC')
            ->orderBy('doctors.is_active', 'DESC')
            ->orderBy('doctors.sort_order', 'ASC')
            ->get(['doctors.id', 'doctors.name', 'departments.dept_name']);

        // Map for consistency with previous dummy structure
        return $doctors_data->map(function ($doc) {
            return [
                'id'   => $doc->id,
                'name' => $doc->name,
            ];
        })->toArray();
    }

    // --------------------------------------------------------------
    // Generate fixed dummy appointments
    // --------------------------------------------------------------
    private function generateAppointmentsStatic()
    {
        $doctors = $this->getDoctors();
        $patients = [
            ['id'=>200,'name'=>'Ramesh Kumar','email'=>'rameshkumar@gmail.com','phone'=>'9052691535'],
            ['id'=>201,'name'=>'Neha Singh','email'=>'nehasingh@gmail.com','phone'=>'9052691536'],
            ['id'=>202,'name'=>'Rahul Mehta','email'=>'rahulmehta@gmail.com','phone'=>'9052691537'],
            ['id'=>203,'name'=>'Anita Sharma','email'=>'anitasharma@gmail.com','phone'=>'9052691538'],
            ['id'=>204,'name'=>'Vikram Singh','email'=>'vikramsingh@gmail.com','phone'=>'9052691539'],
            ['id'=>205,'name'=>'Kiran Gupta','email'=>'kiran@gmail.com','phone'=>'9052691540'],
        ];

        $appointments = [];
        $appointmentNo = 1;
        $paymentNo = 1001;
        $timeSlots = ['09:30 - 10:00','10:00 - 10:30','11:00 - 11:30','12:00 - 12:30'];

        // Assign 2 appointments per doctor (fixed)
        foreach ($doctors as $doctor) {
            for ($i = 0; $i < 8; $i++) {
                $patient = $patients[$i % count($patients)];
                $status = $i % 2 === 0 ? 'success' : 'failed';
                $paymentDate = $status === 'success' ? date('Y-m-d H:i:s', strtotime("2025-01-1".($i+1)." 09:45:00")) : null;

                $appointments[] = [
                    'appointment_no' => 'APT-'.str_pad($appointmentNo++, 3, '0', STR_PAD_LEFT),
                    'doctor_id'      => $doctor['id'],
                    'doctor_name'    => $doctor['name'],
                    'patient_id'     => $patient['id'],
                    'patient_name'   => $patient['name'],
                    'patient_email'  => $patient['email'],
                    'patient_phone'  => $patient['phone'],
                    'date'           => date('Y-m-d', strtotime("2025-01-1".($i+1))),
                    'time'           => $timeSlots[$i % count($timeSlots)],
                    'fee'            => 500 + ($i * 100),
                    'payment_id'     => 'PAY-'.($paymentNo++),
                    'payment_status' => $status,
                    'payment_date'   => $paymentDate,
                ];
            }
        }

        return $appointments;
    }

    // --------------------------------------------------------------
    // Calculate summary for dashboard/cards
    // --------------------------------------------------------------
    private function getSummary($appointments)
    {
        $success = array_filter($appointments, fn($a) => $a['payment_status'] === 'success');
        $failed  = array_filter($appointments, fn($a) => $a['payment_status'] === 'failed');

        return [
            'total_appointments' => count($appointments),
            'success_count'      => count($success),
            'failed_count'       => count($failed),
            'total_amount'       => array_sum(array_column($appointments, 'fee')),
            'success_amount'     => array_sum(array_column($success, 'fee')),
            'failed_amount'      => array_sum(array_column($failed, 'fee')),
            'successPayments'    => $success,
            'failedPayments'     => $failed,
        ];
    }

    // --------------------------------------------------------------
    // Show payment report
    // --------------------------------------------------------------
    public function index()
    {
        $doctors = $this->getDoctors();
        $summaryData = $this->getSummary($this->appointments);

        return view('payment.report', [
            'doctors'      => $doctors,
            'appointments' => $this->appointments,
            'filtered'     => $this->appointments,
            'summaryData'  => $summaryData,
        ]);
    }

    // --------------------------------------------------------------
    // Filter report by doctor, date, status
    // --------------------------------------------------------------
    public function filter(Request $request)
    {
        $filtered = $this->appointments;

        if ($request->doctor) {
            $filtered = array_filter($filtered, fn($a) => $a['doctor_id'] == $request->doctor);
        }

        if ($request->from_date) {
            $filtered = array_filter($filtered, fn($a) => $a['date'] >= $request->from_date);
        }

        if ($request->to_date) {
            $filtered = array_filter($filtered, fn($a) => $a['date'] <= $request->to_date);
        }

        if ($request->status) {
            $filtered = array_filter($filtered, fn($a) => $a['payment_status'] === $request->status);
        }

        $doctors = $this->getDoctors();
        $summaryData = $this->getSummary($filtered);

        return view('payment.report', [
            'doctors'      => $doctors,
            'appointments' => $this->appointments,
            'filtered'     => $filtered,
            'summaryData'  => $summaryData,
        ]);
    }

    // --------------------------------------------------------------
    // Export report placeholder
    // --------------------------------------------------------------
    public function export(Request $request)
    {
        return "Export function not implemented yet.";
    }
}

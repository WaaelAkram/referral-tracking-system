<?php

namespace App\Gateways;

use Illuminate\Support\Facades\DB; // <-- THIS IS THE REQUIRED LINE
use Illuminate\Support\Collection;
use stdClass;

/**
 * A gateway to interact with the external clinic patient database.
 * All logic for querying the 'mysql_referral_test' connection lives here.
 */
class ClinicPatientGateway
{
    protected $connection;

    public function __construct()
    {
        // Set the database connection for this gateway
        $this->connection = DB::connection('mssql_clinic');
    }

    /**
     * Find a patient by their mobile number.
     */
    public function findPatientByMobile(string $mobile): ?stdClass
    {
        return $this->connection->table('patient')->where('mobile', $mobile)->first();
    }

    /**
     * Find a patient by their ID.
     */
    public function findPatientById(int $patientId): ?stdClass
    {
        return $this->connection->table('patient')->where('id', $patientId)->first();
    }

    /**
     * Get the sum of all payments for a given patient ID.
     */
    public function getTotalPaidForPatient(int $patientId): float
    {
        return $this->connection->table('invoice_h')
            ->where('pt_id', $patientId)
            ->sum('net_amnt') ?? 0.0;
    }

    // --- YOUR NEW, CORRECT METHODS ---

    public function findPatientsByIds(array $patientIds): Collection
    {
        if (empty($patientIds)) {
            return collect(); // Return an empty collection if no IDs are provided
        }
        return $this->connection->table('patient')->whereIn('id', $patientIds)->get()->keyBy('id');
    }

    public function getTotalPaidForPatients(array $patientIds): Collection
    {
        if (empty($patientIds)) {
            return collect(); // Return an empty collection if no IDs are provided
        }
        return $this->connection->table('invoice_h')
            ->whereIn('pt_id', $patientIds)
            ->select('pt_id', DB::raw('SUM(net_amnt) as total_paid'))
            ->groupBy('pt_id')
            ->get()->keyBy('pt_id');
    }
   public function getAppointmentsInWindow(string $startTime, string $endTime): Collection
    {
        $today = now()->toDateString();
        $statusesToFetch = [0, 1]; // 0=Unconfirmed, 1=Confirmed

        return $this->connection->table('appointment')
            ->whereDate('app_dt', $today)
            ->whereTime('from_tm', '>=', $startTime)
            ->whereTime('from_tm', '<', $endTime)
            ->whereIn('app_status', $statusesToFetch)
            ->select(
                'id as appointment_id',        // <-- THE CRITICAL FIX: Use the 'id' column
                'pt_name as full_name',
                'mobile',
                'from_tm as appointment_time',
                'app_status'
            )
            ->get();
    }
    



public function getAppointmentsFinishedBetween(\Carbon\Carbon $startDateTime, \Carbon\Carbon $endDateTime): \Illuminate\Support\Collection
{

    return $this->connection->table('appointment')
        ->whereRaw(
            "CAST(app_dt AS DATETIME) + CAST(to_tm AS TIME) BETWEEN ? AND ?",
            [
                $startDateTime->toDateTimeString(),
                $endDateTime->toDateTimeString()
            ]
        )
        ->select(
            'id as appointment_id',
            'pt_name as full_name',
            'mobile'
        )
        ->get();
}
}
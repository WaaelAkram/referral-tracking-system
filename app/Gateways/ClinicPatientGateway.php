<?php

namespace App\Gateways;

use Illuminate\Support\Facades\DB;
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
}

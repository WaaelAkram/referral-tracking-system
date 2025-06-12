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
        $this->connection = DB::connection('mysql_referral_test');
    }

    /**
     * Find a patient by their mobile number.
     *
     * @param string $mobile
     * @return stdClass|null
     */
    public function findPatientByMobile(string $mobile): ?stdClass
    {
        return $this->connection->table('patient')->where('mobile', $mobile)->first();
    }
    
    /**
     * Find a patient by their ID.
     *
     * @param int $patientId
     * @return stdClass|null
     */
    public function findPatientById(int $patientId): ?stdClass
    {
        return $this->connection->table('patient')->where('id', $patientId)->first();
    }

    /**
     * Get the sum of all payments for a given patient ID.
     *
     * @param int $patientId
     * @return float
     */
    public function getTotalPaidForPatient(int $patientId): float
    {
        return $this->connection->table('invoice_h')
            ->where('pt_id', $patientId)
            ->sum('net_amnt') ?? 0.0;
    }
}
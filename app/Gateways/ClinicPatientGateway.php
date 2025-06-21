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
    /**
     * Gets key performance indicators (KPIs) for the clinic.
     * 
     * - Total revenue today
     * - New patients this month
     * - Appointments today
     *
     * @return \stdClass
     */
   public function getKpiData(): \stdClass
    {
        // Use CONVERT to get the date in a comparable format without time.
        $today = now()->format('Y-m-d');
        $startOfMonth = now()->startOfMonth()->format('Y-m-d');

        $kpis = $this->connection->query()
            ->select(
                \Illuminate\Support\Facades\DB::raw("(SELECT SUM(cash + span) FROM invoice_h WHERE CONVERT(date, inv_dt) = '{$today}') as total_revenue_today"),
                \Illuminate\Support\Facades\DB::raw("(SELECT COUNT(id) FROM patient WHERE CONVERT(date, trans_dt) >= '{$startOfMonth}') as new_patients_this_month"),
                \Illuminate\Support\Facades\DB::raw("(SELECT COUNT(id) FROM appointment WHERE CONVERT(date, app_dt) = '{$today}') as appointments_today")
            )
            ->first();

        return $kpis ?? new \stdClass();
    }

    /**
     * Gets total revenue for each of the last 12 months.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getMonthlyRevenue(): \Illuminate\Support\Collection
    {
        $startDate = now()->subMonths(12)->startOfMonth()->format('Y-m-d H:i:s');

        return $this->connection->table('invoice_h')
            ->select(
                // --- FIX: Using CONVERT and string manipulation for YYYY-MM format ---
                \Illuminate\Support\Facades\DB::raw("CONVERT(VARCHAR(7), inv_dt, 120) as month"),
                \Illuminate\Support\Facades\DB::raw('SUM(cash + span) as total_revenue')
            )
            ->where('inv_dt', '>=', $startDate)
            ->groupBy(\Illuminate\Support\Facades\DB::raw("CONVERT(VARCHAR(7), inv_dt, 120)"))
            ->orderBy('month', 'asc')
            ->get();
    }

    /**
     * Gets a count of new vs returning patients for a given period.
     *
     * @return \Illuminate\Support\Collection
     */
     public function getNewVsReturningPatients(int $days = 30): \Illuminate\Support\Collection
    {
        $startDate = now()->subDays($days)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        // ================== THIS IS THE FINAL FIX ==================
        // This query redefines the logic to be exhaustive. An appointment
        // is either "returning" or it falls into the "new" bucket. There are
        // no other possibilities, ensuring the total count will always match.

        $sql = "
            SELECT
                CONVERT(VARCHAR(10), a.app_dt, 120) as date,

                SUM(
                    CASE
                        WHEN p.id IS NOT NULL AND CONVERT(date, a.app_dt) > CONVERT(date, p.trans_dt)
                        THEN 1
                        ELSE 0
                    END
                ) as returning_patients,

                SUM(
                    CASE
                        WHEN p.id IS NULL OR CONVERT(date, a.app_dt) <= CONVERT(date, p.trans_dt)
                        THEN 1
                        ELSE 0
                    END
                ) as new_patients

            FROM
                appointment as a
            LEFT JOIN
                patient as p ON a.pt_id = p.id
            WHERE
                CONVERT(date, a.app_dt) >= ?
                AND CONVERT(date, a.app_dt) <= ?
            GROUP BY
                CONVERT(VARCHAR(10), a.app_dt, 120)
            ORDER BY
                date ASC;
        ";
        // ======================= END OF FIX ========================

        return collect(DB::connection('mssql_clinic')->select($sql, [$startDate, $endDate]));
    }

    /**
     * Gets total revenue generated per doctor for a given period.
     *
     * @return \Illuminate\Support\Collection
     */
public function getRevenuePerDoctor(int $days = 30): \Illuminate\Support\Collection
    {
        // --- CHANGE #1: Prepare the date outside the query for cleaner binding ---
        $startDate = now()->subDays($days)->format('Y-m-d');

        // This raw SQL is the exact query you confirmed works.
        // The `?` is a placeholder for a parameter binding, which is safer.
        $sql = "
            SELECT TOP 10
                d.name_a as doctor_name,
                SUM(i.cash + i.span) as total_revenue
            FROM
                invoice_h as i
            JOIN
                doctor as d ON i.doc_id = d.id
            WHERE
                CONVERT(date, i.inv_dt) >= ?
                AND d.name_a IS NOT NULL
                AND d.name_a <> ''
            GROUP BY
                d.name_a
            ORDER BY
                total_revenue DESC;
        ";

        // --- CHANGE #2: Execute the raw query using select() with bindings ---
        // This is the most reliable way to run a complex query across drivers.
        return collect(\Illuminate\Support\Facades\DB::connection('mssql_clinic')->select($sql, [$startDate]));
    }
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
        $today_yyyymmdd = now()->format('Y/m/d'); 
        $statusesToFetch = [0, 1];

        return $this->connection->table('appointment')
            ->where('app_dt', $today_yyyymmdd) 
            ->whereTime('from_tm', '>=', $startTime)
            ->whereTime('from_tm', '<', $endTime)
            ->whereIn('app_status', $statusesToFetch)
            ->select(
                'id as appointment_id',
                'pt_name as full_name',
                'mobile',
                'from_tm as appointment_time',
                'doc_nm as doctor_name',
                'app_dt as appointment_date', 
                'app_status',
                'trans_dt as created_at' // <-- THIS IS THE REQUIRED CHANGE
            )
            ->get();
    }
    


 /**
     * Replaces the old `getAllAppointmentsForDate`.
     * Fetches appointments that finished within a specific time window.
     * This handles the 'yyyy/mm/dd' text-based date format safely.
     *
     * @param string $startTime 'H:i:s'
     * @param string $endTime 'H:i:s'
     * @return \Illuminate\Support\Collection
     */
    public function getAppointmentsFinishedInWindow(string $startTime, string $endTime): Collection
    {
        // Get today's date in the format the database uses (YYYY/MM/DD)
        $today_yyyymmdd = now()->format('Y/m/d');

        return $this->connection->table('appointment')
            // Match today's date using the text format
            ->where('app_dt', $today_yyyymmdd)
            // Filter by the time part
            ->whereTime('to_tm', '>=', $startTime)
            ->whereTime('to_tm', '<', $endTime)
            ->whereNotNull('to_tm')
            ->select(
                'id as appointment_id',
                'pt_name as full_name',
                'mobile',
                'doc_nm as doctor_name',
                'app_dt',
                'to_tm'
            )
            ->get();
    }
     public function getAverageRevenuePerAppointment(int $days = 30): float
    {
        $startDate = now()->subDays($days)->format('Y-m-d');

        // Get total revenue for the period
        $totalRevenue = $this->connection->table('invoice_h')
            ->where(DB::raw("CONVERT(date, inv_dt)"), '>=', $startDate)
            ->sum(DB::raw('cash + span'));

        // Get total number of appointments for the period
        $totalAppointments = $this->connection->table('appointment')
            ->where(DB::raw("CONVERT(date, app_dt)"), '>=', $startDate)
            ->count();
            
        if ($totalAppointments === 0) {
            return 0.0;
        }

        return $totalRevenue / $totalAppointments;
    }

    // --- NEW METHOD 2: PATIENT RETENTION RATE ---
    /**
     * Calculates the patient retention rate over a given period.
     *
     * @param int $days The number of days to look back.
     * @return float The retention rate as a percentage.
     */
    public function getPatientRetentionRate(int $days = 30): float
    {
        $startDate = now()->subDays($days)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        // This query is similar to getNewVsReturningPatients but without the daily grouping
        $sql = "
            SELECT
                SUM(
                    CASE
                        WHEN p.id IS NOT NULL AND CONVERT(date, a.app_dt) > CONVERT(date, p.trans_dt)
                        THEN 1
                        ELSE 0
                    END
                ) as returning_patients,
                
                SUM(1) as total_appointments
            FROM
                appointment as a
            LEFT JOIN
                patient as p ON a.pt_id = p.id
            WHERE
                CONVERT(date, a.app_dt) >= ?
                AND CONVERT(date, a.app_dt) <= ?;
        ";

        $result = DB::connection('mssql_clinic')->selectOne($sql, [$startDate, $endDate]);

        if (!$result || $result->total_appointments == 0) {
            return 0.0;
        }

        return ($result->returning_patients / $result->total_appointments) * 100;
    }
}
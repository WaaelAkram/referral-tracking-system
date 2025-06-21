<?php

namespace App\Http\Controllers;

use App\Gateways\ClinicPatientGateway;
use Illuminate\Http\Request;
use Carbon\Carbon; // <-- CORRECT: Import at the top with other 'use' statements.

class PublicDashboardController extends Controller
{
    /**
     * Display the English version of the public dashboard.
     */
    public function index(ClinicPatientGateway $gateway)
    {
        // Fetch all data points for the dashboard
        $kpiData = $gateway->getKpiData();
        $monthlyRevenue = $gateway->getMonthlyRevenue();
        $patientMix = $gateway->getNewVsReturningPatients();
        $doctorRevenue = $gateway->getRevenuePerDoctor();
        $arpa = $gateway->getAverageRevenuePerAppointment(30);
        $retentionRate = $gateway->getPatientRetentionRate(30);

        // Package all data into a single array for the view
        $data = [
            'kpis' => $kpiData,
            'monthlyRevenue' => $monthlyRevenue,
            'patientMix' => $patientMix,
            'doctorRevenue' => $doctorRevenue,
            'arpa' => $arpa,
            'retentionRate' => $retentionRate,
        ];

        // Return the public view with the data
        return view('public-dashboard', compact('data'));
    }

    /**
     * Display the Arabic version of the public dashboard.
     */
    public function index_ar(ClinicPatientGateway $gateway)
    {
        // Set the application's locale to Arabic for this request
        app()->setLocale('ar');
        Carbon::setLocale('ar');

        // Fetch all the same data points
        $kpiData = $gateway->getKpiData();
        $monthlyRevenue = $gateway->getMonthlyRevenue();
        $patientMix = $gateway->getNewVsReturningPatients();
        $doctorRevenue = $gateway->getRevenuePerDoctor();
        $arpa = $gateway->getAverageRevenuePerAppointment(30);
        $retentionRate = $gateway->getPatientRetentionRate(30);

        $data = [
            'kpis' => $kpiData,
            'monthlyRevenue' => $monthlyRevenue,
            'patientMix' => $patientMix,
            'doctorRevenue' => $doctorRevenue,
            'arpa' => $arpa,
            'retentionRate' => $retentionRate,
        ];

        // Return the new Arabic view with the data
        return view('public-dashboard-ar', compact('data'));
    }
}
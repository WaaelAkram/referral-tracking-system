<?php

namespace App\Http\Controllers;

use App\Gateways\ClinicPatientGateway;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PublicDashboardController extends Controller
{
    /**
     * Display the English version of the public dashboard.
     */
    public function index(Request $request, ClinicPatientGateway $gateway) // <-- Add Request
    {
        $doctorLimit = $request->input('doctor_limit', 5); // Get limit from URL, default to 5

        // Fetch all data points for the dashboard
        $kpiData = $gateway->getKpiData();
        $monthlyRevenue = $gateway->getMonthlyRevenue();
        $patientMix = $gateway->getNewVsReturningPatients();
        $doctorRevenue = $gateway->getRevenuePerDoctor();
        $retentionRate = $gateway->getPatientRetentionRate(30);
        $arpp = $gateway->getAverageRevenuePerPatient(30);
        $arpa = $gateway->getAverageRevenuePerAppointment(30);
        $avgDoctorRevenue = $gateway->getAverageRevenuePerPatientByDoctor($doctorLimit); // Use the new method

        // Package all data into a single array for the view
        $data = [
            'kpis' => $kpiData,
            'monthlyRevenue' => $monthlyRevenue,
            'patientMix' => $patientMix,
            'doctorRevenue' => $doctorRevenue,
            'retentionRate' => $retentionRate,
            'arpp' => $arpp,
            'arpa' => $arpa,
            'avgDoctorRevenue' => $avgDoctorRevenue, // Add new data
            'currentDoctorLimit' => $doctorLimit, // Pass the current limit to the view
        ];

        // Return the public view with the data
        return view('public-dashboard', compact('data'));
    }

    /**
     * Display the Arabic version of the public dashboard.
     */
    public function index_ar(Request $request, ClinicPatientGateway $gateway) // <-- Add Request
    {
        app()->setLocale('ar');
        Carbon::setLocale('ar');

        $doctorLimit = $request->input('doctor_limit', 5); // Get limit from URL, default to 5

        // Fetch all the same data points
        $kpiData = $gateway->getKpiData();
        $monthlyRevenue = $gateway->getMonthlyRevenue();
        $patientMix = $gateway->getNewVsReturningPatients();
        $doctorRevenue = $gateway->getRevenuePerDoctor();
        $retentionRate = $gateway->getPatientRetentionRate(30);
        $arpp = $gateway->getAverageRevenuePerPatient(30);
        $arpa = $gateway->getAverageRevenuePerAppointment(30);
        $avgDoctorRevenue = $gateway->getAverageRevenuePerPatientByDoctor($doctorLimit); // Use the new method

        $data = [
            'kpis' => $kpiData,
            'monthlyRevenue' => $monthlyRevenue,
            'patientMix' => $patientMix,
            'doctorRevenue' => $doctorRevenue,
            'retentionRate' => $retentionRate,
            'arpp' => $arpp,
            'arpa' => $arpa,
            'avgDoctorRevenue' => $avgDoctorRevenue, // Add new data
            'currentDoctorLimit' => $doctorLimit, // Pass the current limit to the view
        ];

        // Return the new Arabic view with the data
        return view('public-dashboard-ar', compact('data'));
    }
}
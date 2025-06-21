<?php

namespace App\Http\Controllers;

use App\Gateways\ClinicPatientGateway;
use Illuminate\Http\Request;

class PublicDashboardController extends Controller
{
    public function index(ClinicPatientGateway $gateway)
    {
        // Fetch all data points for the dashboard
        $kpiData = $gateway->getKpiData();
        $monthlyRevenue = $gateway->getMonthlyRevenue();
        $patientMix = $gateway->getNewVsReturningPatients();
        $doctorRevenue = $gateway->getRevenuePerDoctor();

        // Package all data into a single array for the view
        $data = [
            'kpis' => $kpiData,
            'monthlyRevenue' => $monthlyRevenue,
            'patientMix' => $patientMix,
            'doctorRevenue' => $doctorRevenue,
        ];

        // Return the public view with the data
        return view('public-dashboard', compact('data'));
    }
}
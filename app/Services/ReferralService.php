<?php

namespace App\Services;

use App\Gateways\ClinicPatientGateway;
use App\Models\Referral;
use App\Models\Referrer;
use Exception;

/**
 * Handles all core business logic for the referral system.
 */
class ReferralService
{
    public function __construct(private ClinicPatientGateway $clinicGateway)
    {
    }

    /**
     * Generates and saves a new referral code for a patient.
     * @throws Exception
     */
    public function generateCodeForPatient(string $phone): array
    {
        $patient = $this->clinicGateway->findPatientByMobile($phone);

        if (!$patient) {
            throw new Exception('Patient not found in clinic records.');
        }

        if (Referrer::where('referrer_patient_id', $patient->id)->exists()) {
            throw new Exception('Referral code already exists for this patient.');
        }

        $referralCode = 'REF' . $patient->id;

        Referrer::create([
            'referrer_patient_id' => $patient->id,
            'referral_code' => $referralCode,
            'referrer_phone' => $phone,
        ]);

        return ['patient' => $patient, 'code' => $referralCode];
    }
    
    /**
     * Checks if a referral is valid without creating it.
     * @throws Exception
     */
    public function checkEligibility(string $referralCode, string $referredMobile): void
    {
        $this->validateReferral($referralCode, $referredMobile);
    }

    /**
     * Validates and creates a new referral in the database.
     * @throws Exception
     */
    public function createReferral(string $referralCode, string $referredMobile): Referral
    {
        $validation = $this->validateReferral($referralCode, $referredMobile);
        $referrer = $validation['referrer'];
        $referredPatient = $validation['referredPatient'];

        $totalPaid = $this->clinicGateway->getTotalPaidForPatient($referredPatient->id);

        return Referral::create([
            'referrer_patient_id' => $referrer->referrer_patient_id,
            'referred_patient_id' => $referredPatient->id,
            'referral_date' => now(),
            'status' => 'pending',
            'reward_issued' => 0,
            'total_paid' => $totalPaid,
            'referral_code_used' => $referralCode,
        ]);
    }
    
    /**
     * Fetches detailed information for a given referrer.
     * @throws Exception
     */
    public function getReferrerInfo(string $searchTerm): array
    {
        $referrer = Referrer::where('referral_code', $searchTerm)
            ->orWhere('referrer_phone', $searchTerm)
            ->first();

        if (!$referrer) {
            throw new Exception('No referrer found with this phone or referral code.');
        }

        $referrals = $referrer->referrals()->with('reward')->get();
        
        $referredPatientDetails = [];
        foreach ($referrals as $referral) {
            $patient = $this->clinicGateway->findPatientById($referral->referred_patient_id);
            $totalPaid = $this->clinicGateway->getTotalPaidForPatient($referral->referred_patient_id);

            $referredPatientDetails[] = (object)[
                'fname_a' => $patient->fname_a ?? 'Unknown',
                'lname_a' => $patient->lname_a ?? '',
                'mobile' => $patient->mobile ?? '',
                'total_paid' => $totalPaid,
                'reward_value' => $referral->reward->reward_value ?? null,
            ];
        }
        
        $referrerPatient = $this->clinicGateway->findPatientById($referrer->referrer_patient_id);

        return [
            'referrer_name' => $referrerPatient ? ($referrerPatient->fname_a . ' ' . $referrerPatient->lname_a) : 'Unknown',
            'referral_code' => $referrer->referral_code,
            'total_referrals' => $referrals->count(),
            'total_rewards' => $referrals->sum('reward.reward_value'),
            'referred_patients' => $referredPatientDetails,
        ];
    }

    /**
     * A private helper to run all validation checks for a potential referral.
     * @throws Exception
     */
    private function validateReferral(string $referralCode, string $referredMobile): array
    {
        $referrer = Referrer::where('referral_code', $referralCode)->first();
        if (!$referrer) {
            throw new Exception('Invalid referral code.');
        }

        $referredPatient = $this->clinicGateway->findPatientByMobile($referredMobile);
        if (!$referredPatient) {
            throw new Exception('Referred patient not found in clinic records.');
        }

        if ($referrer->referrer_phone === $referredPatient->mobile) {
            throw new Exception('Referrer and referred cannot be the same person.');
        }

        if (Referral::where('referred_patient_id', $referredPatient->id)->exists()) {
            throw new Exception('This patient has already been referred.');
        }

        if (Referrer::where('referrer_phone', $referredPatient->mobile)->exists()) {
            throw new Exception('This patient is already a referrer and cannot be referred.');
        }

        return ['referrer' => $referrer, 'referredPatient' => $referredPatient];
    }
}
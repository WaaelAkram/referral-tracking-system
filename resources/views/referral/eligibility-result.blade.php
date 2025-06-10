@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Reward Eligibility Preview</h2>

    <table class="table table-bordered">
        <tr>
            <th>Referral Code</th>
            <td>{{ $referral_code }}</td>
        </tr>

        <tr>
            <th>Referrer</th>
            <td>
                ID: {{ $referrer->referrer_patient_id ?? 'N/A' }}<br>
                Code: {{ $referrer->referral_code }}
            </td>
        </tr>

        <tr>
            <th>Referred Patient</th>
            <td>
                {{ $referred_patient->fname_a }} {{ $referred_patient->sname_a }} {{ $referred_patient->lname_a }}
                - {{ $referred_patient->mobile }}
            </td>
        </tr>

        <tr>
            <th>Eligibility</th>
            <td><strong>Pending</strong> (needs ≥ {{ $reward_threshold }} SAR paid)</td>
        </tr>

        <tr>
            <th>Potential Reward</th>
            <td>{{ $reward_value }} SAR</td>
        </tr>
    </table>

    <a href="{{ route('referral.eligibility.form') }}" class="btn btn-secondary">Back</a>
</div>
@endsection

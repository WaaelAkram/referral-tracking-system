@extends('layouts.app') {{-- Adjust if you use a different layout --}}

@section('content')
<div class="container">
    <h1 class="mb-4">Referral Dashboard</h1>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Referral ID</th>
                <th>Referrer Patient ID</th>
                <th>Referred Patient ID</th>
                <th>Date Referred</th>
                <th>Reward Value</th>
                <th>Status</th>
                <th>Reward Type</th>
                <th>Reward Issued</th>
                <th>Total Paid</th>
                <th>Referral Code Used</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($referrals as $ref)
                <tr>
                    <td>{{ $ref->id }}</td>
                    <td>{{ $ref->referrer_patient_id }}</td>
                    <td>{{ $ref->referred_patient_id }}</td>
                    <td>{{ \Carbon\Carbon::parse($ref->referral_date)->format('Y-m-d') }}</td>
                    <td>{{ $ref->reward_value ?? 'N/A' }}</td>
                    <td>{{ ucfirst($ref->status) }}</td>
                    <td>{{ $ref->reward_type ?? '-' }}</td>
                    <td>{{ $ref->reward_issued ? 'Yes' : 'No' }}</td>
                    <td>{{ $ref->total_paid }}</td>
                    <td>{{ $ref->referral_code_used }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Pagination links --}}
    {{ $referrals->links() }}
</div>
@endsection

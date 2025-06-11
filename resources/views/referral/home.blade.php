@extends('layouts.app')

@section('content')
<div class="container my-5" style="max-width: 900px;">
    <h2 class="mb-4 text-center">Referral Dashboard</h2>

    <div class="row g-4">
        <!-- Eligibility Check Card -->
        <div class="col-md-6">
            <div class="card shadow-sm rounded-3">
                <div class="card-body">
                    <h4 class="card-title mb-3">Check Referral Reward Eligibility</h4>

                    {{-- Alerts --}}
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('referral.eligibility.check') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="referral_code" class="form-label">Referral Code (REF###)</label>
                            <input type="text" class="form-control form-control-lg" name="referral_code" id="referral_code" required value="{{ old('referral_code') }}" placeholder="e.g. REF12345">
                        </div>

                        <div class="mb-4">
                            <label for="mobile" class="form-label">Patient Mobile Number</label>
                            <input type="text" class="form-control form-control-lg" name="mobile" id="mobile" required value="{{ old('mobile') }}" placeholder="e.g. 0553311568">
                        </div>

                        <div class="d-flex gap-3">
                            <button type="submit" name="action" value="check" class="btn btn-primary flex-fill">Check Eligibility</button>
                            <button type="submit" name="action" value="add" class="btn btn-success flex-fill">Add Referral</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Generate Referral Code Card -->
        <div class="col-md-6">
            <div class="card shadow-sm rounded-3">
                <div class="card-body">
                    <h4 class="card-title mb-3">Generate Referral Code</h4>

                    {{-- Errors for generate code --}}
                    @if(session('generate_errors'))
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach(session('generate_errors') as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('referral.generate_code') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="phone" class="form-label">Patient Phone Number</label>
                            <input type="text" id="phone" name="phone" class="form-control form-control-lg" required value="{{ old('phone') }}" placeholder="e.g. 0553311568">
                        </div>
                        <button type="submit" class="btn btn-secondary w-100">Generate Code</button>
                    </form>

                    @if(session('generated_patient'))
                        <div class="card mt-4 border-primary rounded-3 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Patient Info</h5>
                                <p><strong>Name:</strong> {{ session('generated_patient')->fname_a }} {{ session('generated_patient')->sname_a }} {{ session('generated_patient')->lname_a }}</p>
                                <p><strong>Mobile:</strong> {{ session('generated_patient')->mobile }}</p>
                                <p><strong>Patient ID:</strong> {{ session('generated_patient')->id }}</p>
                                <hr>
                                <p><strong>Referral Code:</strong> <span class="badge bg-primary fs-5">REF{{ session('generated_patient')->id }}</span></p>
                                <small class="text-muted">Give this code to the patient.</small>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Referral Info Search Card -->
    <div class="card shadow-sm rounded-3 mt-5">
        <div class="card-body">
            <h4 class="card-title mb-3">Search Referral Info</h4>
            <form method="POST" action="{{ route('referral.info.search') }}" class="d-flex gap-2 flex-wrap align-items-center">
                @csrf
                <input type="text" id="search_term" name="search_term" class="form-control form-control-lg flex-grow-1" required value="{{ old('search_term') }}" placeholder="Phone Number or Referral Code">
                <button type="submit" class="btn btn-secondary btn-lg">Check Referral Info</button>
            </form>

            @if ($errors->has('search_term'))
                <div class="text-danger mt-2">{{ $errors->first('search_term') }}</div>
            @endif

            @if(isset($info))
                <div class="mt-4">
                    <h5>Referral Info</h5>
                    <p><strong>Referrer:</strong> {{ $info['referrer_name'] }}</p>
                    <p><strong>Referral Code:</strong> {{ $info['referral_code'] }}</p>
                    <p><strong>Total Referrals:</strong> {{ $info['total_referrals'] }}</p>
                    <p><strong>Total Rewards Earned:</strong> {{ number_format($info['total_rewards'], 2) }} SAR</p>

                    <h6 class="mt-3">Referred Patients:</h6>
                    <ul class="list-group">
                        @foreach($info['referred_patients'] as $patient)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    {{ $patient->fname_a }} {{ $patient->lname_a }} ({{ $patient->mobile }}) — Paid: {{ number_format($patient->total_paid, 2) }} SAR
                                </div>
                                <div>
                                    {!! $patient->reward_value ? '<span class="text-success">✅ ' . $patient->reward_value . ' SAR</span>' : '<span class="text-danger">❌ Not Yet</span>' !!}
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

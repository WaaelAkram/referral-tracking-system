@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <h4 class="mb-3" style="margin-left: 0;">Check Referral Reward Eligibility</h4>

    <div class="row">
        <!-- Left column: Eligibility Check Form -->
        <div class="col-md-6">
            {{-- Errors and messages --}}
            @if ($errors->any())
                <div class="alert alert-danger w-100" style="max-width: 400px;">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('eligibility_result'))
                <div class="alert alert-success w-100" style="max-width: 400px;">
                    {{ session('eligibility_result') }}
                </div>
            @endif

            <form method="POST" action="{{ route('referral.eligibility.check') }}" class="w-100" style="max-width: 400px; margin-left: 0;">
                @csrf

                <div class="mb-2">
                    <label for="referral_code" class="form-label">Referral Code (REF###)</label>
                    <input type="text" class="form-control form-control-sm" name="referral_code" id="referral_code" required value="{{ old('referral_code') }}">
                </div>

                <div class="mb-2">
                    <label for="mobile" class="form-label">Patient Mobile Number</label>
                    <input type="text" class="form-control form-control-sm" name="mobile" id="mobile" required value="{{ old('mobile') }}">
                </div>

                <button type="submit" name="action" value="check" class="btn btn-primary btn-sm me-2">Check Eligibility</button>
                <button type="submit" name="action" value="add" class="btn btn-success btn-sm">Add Referral</button>
            </form>
        </div>

        <!-- Right column: Generate Referral Code -->
        <div class="col-md-6">
            <h4>Generate Referral Code</h4>

            {{-- Display errors for generate code form --}}
            @if(session('generate_errors'))
                <div class="alert alert-danger w-100" style="max-width: 400px;">
                    <ul class="mb-0">
                        @foreach(session('generate_errors') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('referral.generate_code') }}" class="w-100" style="max-width: 400px;">
                @csrf
                <div class="mb-3">
                    <label for="patient_phone" class="form-label">Patient Phone Number</label>
                    <input type="text" id="phone" name="phone" class="form-control form-control-sm" required value="{{ old('phone') }}">

                </div>
                <button type="submit" class="btn btn-secondary btn-sm">Generate Code</button>
            </form>

            @if(session('generated_patient'))
                <div class="card mt-3" style="max-width: 400px;">
                    <div class="card-body">
                        <h5 class="card-title">Patient Info</h5>
                        <p><strong>Name:</strong> {{ session('generated_patient')->fname_a }} {{ session('generated_patient')->sname_a }} {{ session('generated_patient')->lname_a }}</p>
                        <p><strong>Mobile:</strong> {{ session('generated_patient')->mobile }}</p>
                        <p><strong>Patient ID:</strong> {{ session('generated_patient')->id }}</p>
                        <hr>
                        <p><strong>Referral Code:</strong> <span class="badge bg-primary">REF{{ session('generated_patient')->id }}</span></p>
                        <small class="text-muted">Give this code to the patient.</small>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

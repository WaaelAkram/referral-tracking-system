@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <h3 class="mb-3" style="margin-left: 0;">Check Referral Reward Eligibility</h3>

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

    @if(isset($result))
        <div class="alert alert-success w-100" style="max-width: 400px;">{{ $result }}</div>
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

@endsection

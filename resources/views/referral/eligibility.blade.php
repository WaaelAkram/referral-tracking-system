@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Referral Reward Eligibility</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('referral.eligibility.check') }}">
        @csrf
        <div class="mb-3">
            <label for="referral_code" class="form-label">Referral Code (REF###)</label>
            <input type="text" name="referral_code" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="phone" class="form-label">Referred Patient Phone Number</label>
            <input type="text" name="phone" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Check Eligibility</button>
    </form>
</div>
@endsection

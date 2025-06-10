<div class="card mb-4">
    <div class="card-header">Check Reward Eligibility</div>
    <div class="card-body">
        <form method="POST" action="{{ route('referral.eligibility.check') }}">
            @csrf
            <div class="row mb-3">
                <div class="col">
                    <label for="referral_code" class="form-label">Referral Code</label>
                    <input type="text" id="referral_code" class="form-control form-control-sm" name="referral_code" required>
                </div>
                <div class="col">
                    <label for="mobile" class="form-label">Patient Phone</label>
                    <input type="text" id="mobile" class="form-control form-control-sm" name="mobile" required>
                </div>
            </div>
            <button type="submit" class="btn btn-success btn-sm">Check Eligibility</button>
        </form>
    </div>
</div>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Referral Management Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- ALERTS SECTION -->
            @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
            <!-- END ALERTS -->

            <!-- REFERRAL TOOLS GRID -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Eligibility Check Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Check & Add Referral</h3>
                    <form method="POST" action="{{ route('referral.eligibility.check') }}">
                        @csrf
                        <div class="mb-4">
                            <label for="referral_code" class="block text-sm font-medium text-gray-700">Referral Code (REF###)</label>
                            <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" name="referral_code" id="referral_code" required value="{{ old('referral_code') }}">
                        </div>
                        <div class="mb-4">
                            <label for="mobile" class="block text-sm font-medium text-gray-700">Patient Mobile</label>
                            <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" name="mobile" id="mobile" required value="{{ old('mobile') }}">
                        </div>
                        <div class="flex gap-4">
                            <button type="submit" name="action" value="check" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">Check</button>
                            <button type="submit" name="action" value="add" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">Add Referral</button>
                        </div>
                    </form>
                </div>

                <!-- Generate Code Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Generate Referral Code</h3>
                    <form method="POST" action="{{ route('referral.generate_code') }}">
                        @csrf
                        <div class="mb-4">
                            <label for="phone" class="block text-sm font-medium text-gray-700">Patient Phone</label>
                            <input type="text" id="phone" name="phone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required value="{{ old('phone') }}">
                        </div>
                        <button type="submit" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700">Generate Code</button>
                    </form>
                    @if(session('generated_code'))
                        <div class="mt-4 border-t pt-4">
                            <p><strong>Patient:</strong> {{ session('generated_patient')->fname_a }} {{ session('generated_patient')->lname_a }}</p>
                            <p><strong>Generated Code:</strong> 
                                <span class="font-bold text-lg text-indigo-600">{{ session('generated_code') }}</span>
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- SEARCH AND INFO DISPLAY -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Search Referrer Info</h3>
                <form method="POST" action="{{ route('referral.info.search') }}" class="flex gap-4 items-center">
                    @csrf
                    <input type="text" name="search_term" class="flex-grow block w-full rounded-md border-gray-300 shadow-sm" required value="{{ old('search_term', request()->query('search_term')) }}" placeholder="Enter Phone Number or Referral Code">
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">Search</button>
                </form>

                @if(isset($info))
                    <div class="mt-6 border-t pt-4">
                        <h4 class="font-bold text-md">Results for: {{ $info['referrer_name'] }} ({{ $info['referral_code'] }})</h4>
                        <p><strong>Total Referrals:</strong> {{ $info['total_referrals'] }}</p>
                        <p><strong>Total Rewards Earned:</strong> {{ number_format($info['total_rewards'], 2) }} SAR</p>
                        <h5 class="font-semibold mt-3">Referred Patients:</h5>
                        <ul class="list-disc list-inside mt-2 space-y-1">
                            @forelse($info['referred_patients'] as $patient)
                                <li>{{ $patient->fname_a }} {{ $patient->lname_a }} (Paid: {{ number_format($patient->total_paid, 2) }} SAR)
                                    @if($patient->reward_value)
                                        <span class="ml-2 text-green-600 font-bold">✓ Rewarded ({{ $patient->reward_value }} SAR)</span>
                                    @else
                                        <span class="ml-2 text-yellow-600">▪ Pending</span>
                                    @endif
                                </li>
                            @empty
                                <li>No patients referred yet.</li>
                            @endforelse
                        </ul>
                    </div>
                @endif
            </div>

            <!-- ALL REFERRALS LIST -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">All Referrals Log (Total: {{ $referrals->total() }})</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 border">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Referrer ID</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Referred ID</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Code Used</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($referrals as $ref)
                                    <tr>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-600">{{ \Carbon\Carbon::parse($ref->referral_date)->format('Y-m-d') }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-600">{{ $ref->referrer_patient_id }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-600">{{ $ref->referred_patient_id }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $ref->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">{{ ucfirst($ref->status) }}</span></td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-600">{{ $ref->referral_code_used }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-2 text-center text-gray-500">No referrals found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $referrals->links() }}</div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
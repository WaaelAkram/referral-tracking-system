<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Referral Management Tools') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- ALERTS SECTION -->
            @if ($errors->any())
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('success') || session('success_message'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative" role="alert">
                    <span class="block sm:inline">{{ session('success') ?? session('success_message') }}</span>
                </div>
            @endif
            <!-- END ALERTS -->

            <!-- MAIN TOOLS GRID -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- LEFT COLUMN: TWO CARDS -->
                <div class="space-y-8">
                    <!-- Card 1: Check & Add Referral -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Check Eligibility & Add Referral</h3>
                        <form method="POST" action="{{ route('referral.eligibility.check') }}">
                            @csrf
                            <div class="mb-4">
                                <x-input-label for="referral_code" value="Referral Code (REF###)" />
                                <x-text-input id="referral_code" class="block mt-1 w-full" type="text" name="referral_code" :value="old('referral_code')" required />
                            </div>
                            <div class="mb-4">
                                <x-input-label for="mobile" value="New Patient's Mobile" />
                                <x-text-input id="mobile" class="block mt-1 w-full" type="text" name="mobile" :value="old('mobile')" required />
                            </div>
                            <div class="flex gap-4">
                                <x-secondary-button type="submit" name="action" value="check" class="w-full justify-center">Check Only</x-secondary-button>
                                <x-primary-button type="submit" name="action" value="add" class="w-full justify-center">Add Referral</x-primary-button>
                            </div>
                        </form>
                    </div>

                    <!-- Card 2: Generate Referral Code -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Generate New Referral Code</h3>
                        <form method="POST" action="{{ route('referral.generate_code') }}">
                            @csrf
                            <div class="mb-4">
                                <x-input-label for="phone" value="Existing Patient's Phone" />
                                <x-text-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone')" required />
                            </div>
                            <x-primary-button class="w-full justify-center">Generate Code</x-primary-button>
                        </form>

                        @if(session('generated_code'))
                            <div class="mt-4 border-t pt-4 text-center">
                                <p><strong>Patient:</strong> {{ session('generated_patient')->fname_a }} {{ session('generated_patient')->lname_a }}</p>
                                <p class="mt-2"><strong>Generated Code:</strong> 
                                    <span class="text-xl font-bold text-indigo-600 bg-indigo-100 px-3 py-1 rounded-md">{{ session('generated_code') }}</span>
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- RIGHT COLUMN: SEARCH AND INFO DISPLAY -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Search Referrer Information</h3>
                    <form method="POST" action="{{ route('referral.info.search') }}" class="flex gap-2 items-center">
                        @csrf
                        <x-text-input id="search_term" name="search_term" class="block w-full" :value="old('search_term')" required placeholder="Phone or Referral Code" />
                        <x-primary-button>Search</x-primary-button>
                    </form>

                    @if(isset($info))
                        <div class="mt-6 border-t pt-4">
                            <h4 class="font-bold text-md">Results for: {{ $info['referrer_name'] }} ({{ $info['referral_code'] }})</h4>
                            <div class="mt-2 text-sm space-y-1">
                                <p><strong>Total Referrals Made:</strong> {{ $info['total_referrals'] }}</p>
                                <p><strong>Total Rewards Earned:</strong> <span class="font-semibold">{{ number_format($info['total_rewards'], 2) }} SAR</span></p>
                            </div>
                            <h5 class="font-semibold mt-4 mb-2">Referred Patients Details:</h5>
                            <ul class="list-disc list-inside space-y-2 text-sm">
                                @forelse($info['referred_patients'] as $patient)
                                    <li>
                                        {{ $patient->fname_a }} {{ $patient->lname_a }}
                                        <span class="text-gray-500">(Paid: {{ number_format($patient->total_paid, 2) }} SAR)</span>
                                        @if($patient->reward_value)
                                            <span class="ml-2 font-bold text-green-600">✓ Rewarded ({{ $patient->reward_value }} SAR)</span>
                                        @else
                                            <span class="ml-2 text-yellow-600">▪ Pending Reward</span>
                                        @endif
                                    </li>
                                @empty
                                    <li>No patients have been referred by this person yet.</li>
                                @endforelse
                            </ul>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
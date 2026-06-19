<x-app-layout>
    <div class="max-w-6xl mx-auto px-4 py-6">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-semibold text-gray-800">
                    Punch Details
                </h2>
                <p class="text-sm text-gray-500">
                    {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
                </p>
            </div>

            <a href="{{ route('attendance.my') }}"
               class="px-4 py-2 bg-gray-200 hover:bg-gray-300 
                      text-gray-800 rounded-lg text-sm font-medium transition">
                ← Back
            </a>
        </div>

        {{-- Punch Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($logs as $log)

                <div class="bg-white shadow rounded-xl overflow-hidden">

                    {{-- Punch Type Header --}}
                    <div class="px-5 py-3 
                        {{ $log->punch_type === 'in' ? 'bg-green-100' : 'bg-red-100' }}">
                        <span class="text-sm font-semibold
                            {{ $log->punch_type === 'in' ? 'text-green-700' : 'text-red-700' }}">
                            {{ strtoupper($log->punch_type) }} PUNCH
                        </span>
                    </div>

                    {{-- Punch Image --}}
                    <div class="p-5">
                        <img src="{{ asset('storage/'.$log->image) }}"
                             class="w-full h-48 object-cover rounded-lg border"
                             alt="Punch Image">

                        {{-- Details --}}
                        <div class="mt-4 space-y-2 text-sm text-gray-700">

                            <div class="flex justify-between">
                                <span class="font-medium">Time</span>
                                <span>
                                    {{ $log->date }}
                                </span>
                            </div>

                            <div class="flex justify-between">
                                <span class="font-medium">Latitude</span>
                                <span>{{ $log->latitude }}</span>
                            </div>

                            <div class="flex justify-between">
                                <span class="font-medium">Longitude</span>
                                <span>{{ $log->longitude }}</span>
                            </div>

                        </div>

                        {{-- Map Button --}}
                        <div class="mt-4">
                            <a target="_blank"
                               href="https://www.google.com/maps?q={{ $log->latitude }},{{ $log->longitude }}"
                               class="block text-center px-4 py-2 bg-blue-600 
                                      hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition">
                                View Location on Map
                            </a>
                        </div>
                    </div>
                </div>

            @empty
                <div class="col-span-full text-center text-gray-400 py-10">
                    No punch records found for this date
                </div>
            @endforelse
        </div>

    </div>
</x-app-layout>

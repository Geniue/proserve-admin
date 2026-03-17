<div>
    @php
        $record = $getRecord();
        $status = $record->status;
        $steps = [
            'pending' => ['label' => 'Pending', 'icon' => 'clock'],
            'confirmed' => ['label' => 'Confirmed', 'icon' => 'check-circle'],
            'assigned' => ['label' => 'Assigned', 'icon' => 'user-plus'],
            'in_progress' => ['label' => 'In Progress', 'icon' => 'cog'],
            'completed' => ['label' => 'Completed', 'icon' => 'check-badge'],
        ];

        $cancelledStates = ['cancelled', 'refunded'];
        $isCancelled = in_array($status, $cancelledStates);

        $statusOrder = array_keys($steps);
        $currentIndex = array_search($status, $statusOrder);
        if ($currentIndex === false) {
            // Handle states like 'accepted', 'waiting_for_client', etc.
            if (in_array($status, ['accepted'])) $currentIndex = 2;
            elseif (in_array($status, ['waiting_for_client', 'started'])) $currentIndex = 3;
            else $currentIndex = 0;
        }
    @endphp

    @if($isCancelled)
        <div class="rounded-xl border-2 {{ $status === 'cancelled' ? 'border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-950' : 'border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-950' }} p-4">
            <div class="flex items-center gap-3">
                <div class="rounded-full {{ $status === 'cancelled' ? 'bg-red-500' : 'bg-amber-500' }} p-2">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @if($status === 'cancelled')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                        @endif
                    </svg>
                </div>
                <div>
                    <p class="font-bold {{ $status === 'cancelled' ? 'text-red-800 dark:text-red-300' : 'text-amber-800 dark:text-amber-300' }}">
                        Booking {{ ucfirst($status) }}
                    </p>
                    @if($record->cancellation_reason)
                        <p class="text-sm {{ $status === 'cancelled' ? 'text-red-600 dark:text-red-400' : 'text-amber-600 dark:text-amber-400' }}">
                            {{ $record->cancellation_reason }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="relative">
            {{-- Progress bar --}}
            <div class="flex items-center justify-between">
                @foreach($steps as $key => $step)
                    @php
                        $stepIndex = array_search($key, $statusOrder);
                        $isCompleted = $stepIndex < $currentIndex;
                        $isCurrent = $stepIndex === $currentIndex;
                        $isFuture = $stepIndex > $currentIndex;
                    @endphp
                    <div class="flex flex-col items-center flex-1 relative">
                        {{-- Connector line --}}
                        @if(!$loop->first)
                            <div class="absolute top-5 right-1/2 w-full h-[3px] -translate-x-0
                                {{ $isCompleted || $isCurrent ? 'bg-emerald-500' : 'bg-gray-200 dark:bg-gray-700' }}">
                            </div>
                        @endif

                        {{-- Circle --}}
                        <div class="relative z-10 w-10 h-10 rounded-full flex items-center justify-center transition-all
                            {{ $isCompleted ? 'bg-emerald-500 shadow-lg shadow-emerald-200 dark:shadow-emerald-900' : '' }}
                            {{ $isCurrent ? 'bg-blue-500 shadow-lg shadow-blue-200 dark:shadow-blue-900 ring-4 ring-blue-100 dark:ring-blue-900' : '' }}
                            {{ $isFuture ? 'bg-gray-200 dark:bg-gray-700' : '' }}">

                            @if($isCompleted)
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                            @elseif($isCurrent)
                                <div class="w-3 h-3 bg-white rounded-full animate-pulse"></div>
                            @else
                                <div class="w-3 h-3 bg-gray-400 dark:bg-gray-500 rounded-full"></div>
                            @endif
                        </div>

                        {{-- Label --}}
                        <p class="mt-2 text-xs font-semibold text-center
                            {{ $isCompleted ? 'text-emerald-600 dark:text-emerald-400' : '' }}
                            {{ $isCurrent ? 'text-blue-600 dark:text-blue-400' : '' }}
                            {{ $isFuture ? 'text-gray-400 dark:text-gray-500' : '' }}">
                            {{ $step['label'] }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

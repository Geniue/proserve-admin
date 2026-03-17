<div class="space-y-1">
    @php
        $events = $getRecord()->events()->orderBy('created_at', 'desc')->get();
    @endphp

    @if($events->isEmpty())
        <div class="text-center py-8 text-gray-400 dark:text-gray-500">
            <x-filament::icon icon="heroicon-o-clock" class="mx-auto h-10 w-10 mb-2 opacity-40" />
            <p class="text-sm font-medium">No tracking events yet</p>
        </div>
    @else
        <div class="relative">
            {{-- Vertical line --}}
            <div class="absolute left-[19px] top-3 bottom-3 w-[2px] bg-gray-200 dark:bg-gray-700"></div>

            <div class="space-y-0">
                @foreach($events as $event)
                    @php
                        $colorMap = [
                            'emerald' => ['bg' => 'bg-emerald-500', 'ring' => 'ring-emerald-100 dark:ring-emerald-900', 'badge' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400', 'text' => 'text-emerald-700 dark:text-emerald-400'],
                            'red' => ['bg' => 'bg-red-500', 'ring' => 'ring-red-100 dark:ring-red-900', 'badge' => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400', 'text' => 'text-red-700 dark:text-red-400'],
                            'amber' => ['bg' => 'bg-amber-500', 'ring' => 'ring-amber-100 dark:ring-amber-900', 'badge' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400', 'text' => 'text-amber-700 dark:text-amber-400'],
                            'blue' => ['bg' => 'bg-blue-500', 'ring' => 'ring-blue-100 dark:ring-blue-900', 'badge' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400', 'text' => 'text-blue-700 dark:text-blue-400'],
                            'indigo' => ['bg' => 'bg-indigo-500', 'ring' => 'ring-indigo-100 dark:ring-indigo-900', 'badge' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400', 'text' => 'text-indigo-700 dark:text-indigo-400'],
                            'sky' => ['bg' => 'bg-sky-500', 'ring' => 'ring-sky-100 dark:ring-sky-900', 'badge' => 'bg-sky-50 text-sky-700 dark:bg-sky-900/30 dark:text-sky-400', 'text' => 'text-sky-700 dark:text-sky-400'],
                            'gray' => ['bg' => 'bg-gray-400', 'ring' => 'ring-gray-100 dark:ring-gray-800', 'badge' => 'bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-400', 'text' => 'text-gray-600 dark:text-gray-400'],
                        ];
                        $c = $colorMap[$event->color] ?? $colorMap['gray'];
                    @endphp

                    <div class="relative flex gap-4 pb-6 last:pb-0 group">
                        {{-- Dot --}}
                        <div class="relative z-10 flex-shrink-0">
                            <div class="w-10 h-10 rounded-full {{ $c['bg'] }} ring-4 {{ $c['ring'] }} flex items-center justify-center shadow-sm">
                                @if($event->event_type === 'status_change')
                                    @switch($event->to_status)
                                        @case('pending')
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            @break
                                        @case('confirmed')
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            @break
                                        @case('assigned')
                                        @case('accepted')
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                            @break
                                        @case('in_progress')
                                        @case('started')
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            @break
                                        @case('completed')
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                                            @break
                                        @case('cancelled')
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            @break
                                        @default
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                    @endswitch
                                @elseif($event->event_type === 'payment')
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                @else
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                @endif
                            </div>
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 min-w-0 pt-1.5">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white leading-tight">
                                        {{ $event->description }}
                                    </p>
                                    @if($event->event_type === 'status_change' && $event->to_status)
                                        <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded-md text-xs font-medium {{ $c['badge'] }}">
                                            {{ strtoupper(str_replace('_', ' ', $event->to_status)) }}
                                        </span>
                                    @endif
                                </div>
                                <div class="flex-shrink-0 text-right">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                        {{ $event->created_at->format('M j, g:i A') }}
                                    </p>
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5">
                                        {{ $event->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                by {{ $event->performed_by }}
                            </p>
                            @if($event->metadata)
                                <div class="mt-2 p-2 rounded bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                                    @foreach($event->metadata as $key => $value)
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                            {{ is_array($value) ? json_encode($value) : $value }}
                                        </span>
                                        @if(!$loop->last) <span class="text-gray-300 dark:text-gray-600 mx-1">·</span> @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<div class="space-y-4 max-h-96 overflow-y-auto">
    @forelse($logs as $log)
        <div class="flex items-start gap-4 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <div class="flex-shrink-0 w-10 h-10 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center">
                <x-heroicon-o-paint-brush class="w-5 h-5 text-primary-600 dark:text-primary-400" />
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                        {{ $log->getFieldLabel() }}
                    </span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $log->changed_at->diffForHumans() }}
                    </span>
                </div>
                <div class="mt-1 flex items-center gap-2 text-sm">
                    <div class="flex items-center gap-1">
                        <div class="w-4 h-4 rounded border border-gray-300" style="background-color: {{ $log->old_value }}"></div>
                        <span class="font-mono text-xs text-gray-500">{{ $log->old_value }}</span>
                    </div>
                    <x-heroicon-o-arrow-right class="w-4 h-4 text-gray-400" />
                    <div class="flex items-center gap-1">
                        <div class="w-4 h-4 rounded border border-gray-300" style="background-color: {{ $log->new_value }}"></div>
                        <span class="font-mono text-xs text-gray-900 dark:text-gray-100">{{ $log->new_value }}</span>
                    </div>
                </div>
                @if($log->admin)
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        by {{ $log->admin->name }}
                    </p>
                @endif
            </div>
        </div>
    @empty
        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
            <x-heroicon-o-clock class="w-12 h-12 mx-auto mb-2 opacity-50" />
            <p>No changes recorded yet.</p>
        </div>
    @endforelse
</div>

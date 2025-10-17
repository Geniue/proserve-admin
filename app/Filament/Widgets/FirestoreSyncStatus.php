<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\FirestoreSyncLog;
use App\Models\FirestoreSyncStatus as SyncStatusModel;

class FirestoreSyncStatus extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $recentSyncs = FirestoreSyncLog::where('attempted_at', '>=', now()->subHour())->count();
        $failedSyncs = FirestoreSyncLog::where('status', 'failed')
            ->where('attempted_at', '>=', now()->subDay())
            ->count();
        
        $lastSync = SyncStatusModel::max('last_sync_at');
        $lastSyncTime = $lastSync ? \Carbon\Carbon::parse($lastSync)->diffForHumans() : 'Never';

        return [
            Stat::make('Recent Syncs (1h)', $recentSyncs)
                ->description('Firestore operations in the last hour')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            
            Stat::make('Failed Syncs (24h)', $failedSyncs)
                ->description($failedSyncs > 0 ? 'Requires attention' : 'All syncs successful')
                ->descriptionIcon($failedSyncs > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($failedSyncs > 0 ? 'danger' : 'success'),
            
            Stat::make('Last Sync', $lastSyncTime)
                ->description('Most recent Firestore sync')
                ->descriptionIcon('heroicon-m-clock')
                ->color('primary'),
        ];
    }
}


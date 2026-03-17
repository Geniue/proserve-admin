<?php

namespace App\Filament\Resources\ServiceBookings\Pages;

use App\Filament\Resources\ServiceBookings\ServiceBookingResource;
use App\Mail\BookingReceiptMail;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Mail;

class ViewServiceBooking extends ViewRecord
{
    protected static string $resource = ServiceBookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewReceipt')
                ->label('View Receipt')
                ->icon('heroicon-o-document-text')
                ->color('info')
                ->url(fn () => route('receipts.view', $this->record))
                ->openUrlInNewTab()
                ->visible(fn () => $this->record->status === 'completed'),

            Action::make('downloadReceipt')
                ->label('Download PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->url(fn () => route('receipts.pdf', $this->record))
                ->openUrlInNewTab()
                ->visible(fn () => $this->record->status === 'completed'),

            Action::make('emailReceipt')
                ->label('Email Receipt')
                ->icon('heroicon-o-envelope')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Send Receipt via Email')
                ->modalDescription(fn () => "Send receipt to {$this->record->customer?->email}?")
                ->visible(fn () => $this->record->status === 'completed' && $this->record->customer?->email)
                ->action(function () {
                    Mail::to($this->record->customer->email)
                        ->send(new BookingReceiptMail($this->record));
                    Notification::make()
                        ->title('Receipt emailed to ' . $this->record->customer->email)
                        ->success()
                        ->send();
                }),

            EditAction::make(),
        ];
    }
}

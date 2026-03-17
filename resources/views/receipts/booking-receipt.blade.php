<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Receipt #{{ $booking->booking_number }}</title>
    <style>
        @page { margin: 40px 50px; }
        body { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; color: #1e293b; font-size: 12px; line-height: 1.5; }

        /* RTL support - mpdf handles this automatically */

        /* Header */
        .header-table { width: 100%; margin-bottom: 0; }
        .header-table td { vertical-align: top; padding: 0; }
        .brand-name { font-size: 32px; font-weight: bold; color: #2563eb; letter-spacing: -1px; }
        .brand-tagline { font-size: 10px; color: #94a3b8; margin-top: 2px; }
        .receipt-title { font-size: 24px; font-weight: bold; color: #1e293b; text-transform: uppercase; letter-spacing: 1px; }
        .receipt-number { font-size: 12px; color: #64748b; margin-top: 4px; }

        .divider { border: none; border-top: 3px solid #2563eb; margin: 16px 0 24px 0; }

        /* Status Badge */
        .status-badge { display: inline-block; padding: 3px 14px; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; border-radius: 10px; }
        .status-completed { background: #dcfce7; color: #166534; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        .status-pending { background: #fef3c7; color: #92400e; }

        /* Info Grid using tables */
        .info-grid { width: 100%; border-collapse: separate; border-spacing: 10px 0; margin-bottom: 24px; }
        .info-card { background: #f8fafc; border: 1px solid #e2e8f0; padding: 14px 16px; vertical-align: top; width: 33.33%; }
        .info-label { font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; margin-bottom: 6px; }
        .info-value { font-size: 13px; font-weight: bold; color: #1e293b; margin-top: 4px; }
        .info-sub { font-size: 10px; color: #64748b; margin-top: 2px; }

        /* Service Table */
        .service-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .service-table th { background: #1e293b; color: #ffffff; padding: 10px 16px; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; text-align: left; }
        .service-table th.amount { text-align: right; }
        .service-table td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
        .service-table td.amount { text-align: right; font-weight: bold; font-size: 13px; }
        .service-name { font-weight: bold; font-size: 13px; color: #1e293b; }
        .service-category { font-size: 10px; color: #64748b; margin-top: 2px; }
        .service-address { font-size: 10px; color: #64748b; margin-top: 4px; }

        /* Totals */
        .totals-table { width: 260px; margin-left: auto; margin-bottom: 28px; border-collapse: collapse; }
        .totals-table td { padding: 6px 0; font-size: 12px; }
        .totals-table .label { color: #64748b; text-align: left; }
        .totals-table .value { text-align: right; font-weight: 600; color: #1e293b; }
        .totals-table .subtotal-row td { border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; }
        .totals-table .discount-row .value { color: #16a34a; }
        .totals-table .total-row td { border-top: 2px solid #1e293b; padding-top: 12px; font-size: 16px; font-weight: bold; }
        .totals-table .total-row .label { color: #1e293b; }

        /* Payment Info */
        .payment-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; background: #f0fdf4; border: 1px solid #bbf7d0; }
        .payment-table td { padding: 14px 16px; vertical-align: top; }
        .payment-label { font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; color: #166534; }
        .payment-value { font-size: 14px; font-weight: bold; color: #166534; margin-top: 4px; }

        /* Rating */
        .rating-box { background: #fefce8; border: 1px solid #fde68a; padding: 14px 16px; margin-bottom: 24px; }
        .rating-stars { font-size: 16px; color: #f59e0b; }
        .rating-score { font-size: 12px; color: #92400e; font-weight: bold; }
        .rating-text { font-size: 11px; color: #92400e; margin-top: 6px; font-style: italic; }

        /* Notes */
        .notes-box { background: #f8fafc; border: 1px solid #e2e8f0; padding: 14px 16px; margin-bottom: 24px; }
        .notes-title { font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; margin-bottom: 4px; }
        .notes-content { font-size: 11px; color: #475569; }

        /* Timeline */
        .timeline-title { font-size: 12px; font-weight: bold; color: #1e293b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; }
        .timeline-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        .timeline-table td { padding: 8px 12px; font-size: 11px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
        .timeline-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 6px; }
        .dot-blue { background-color: #2563eb; }
        .dot-green { background-color: #16a34a; }
        .dot-gray { background-color: #94a3b8; }
        .timeline-event { font-weight: 600; color: #1e293b; }
        .timeline-time { font-size: 10px; color: #94a3b8; }
        .timeline-by { font-size: 10px; color: #64748b; }

        /* Footer */
        .footer { border-top: 1px solid #e2e8f0; padding-top: 20px; text-align: center; margin-top: 20px; }
        .footer-brand { font-size: 16px; font-weight: bold; color: #2563eb; margin-bottom: 4px; }
        .footer-text { font-size: 10px; color: #94a3b8; }
    </style>
</head>
<body>

    {{-- ===== HEADER ===== --}}
    <table class="header-table">
        <tr>
            <td style="width: 50%;">
                <div class="brand-name">PUMP</div>
                <div class="brand-tagline">Professional Maintenance Services</div>
            </td>
            <td style="width: 50%; text-align: right;">
                <div class="receipt-title">Receipt</div>
                <div class="receipt-number">#{{ $booking->booking_number }}</div>
                <div style="margin-top: 6px;">
                    <span class="status-badge status-{{ $booking->status }}">{{ strtoupper($booking->status) }}</span>
                </div>
            </td>
        </tr>
    </table>

    <hr class="divider">

    {{-- ===== INFO CARDS ===== --}}
    <table class="info-grid">
        <tr>
            <td class="info-card">
                <div class="info-label">Customer</div>
                <div class="info-value">{{ $booking->customer?->name ?? 'N/A' }}</div>
                @if($booking->customer?->email)
                    <div class="info-sub">{{ $booking->customer->email }}</div>
                @endif
                @if($booking->customer?->phone)
                    <div class="info-sub">{{ $booking->customer->phone }}</div>
                @endif
            </td>
            <td class="info-card">
                <div class="info-label">Service Date</div>
                <div class="info-value">{{ $booking->scheduled_at?->format('M j, Y') ?? 'N/A' }}</div>
                <div class="info-sub">{{ $booking->scheduled_at?->format('g:i A') ?? '' }}</div>
                @if($booking->completed_at)
                    <div class="info-sub" style="margin-top: 6px; color: #166534; font-weight: bold;">
                        Completed: {{ $booking->completed_at->format('M j, Y') }}
                    </div>
                @endif
            </td>
            <td class="info-card">
                <div class="info-label">Provider</div>
                <div class="info-value">{{ $booking->provider?->user?->name ?? 'Not Assigned' }}</div>
                @if($booking->provider?->user?->phone)
                    <div class="info-sub">{{ $booking->provider->user->phone }}</div>
                @endif
                @if($booking->provider?->user?->email)
                    <div class="info-sub">{{ $booking->provider->user->email }}</div>
                @endif
            </td>
        </tr>
    </table>

    {{-- ===== SERVICE DETAILS ===== --}}
    <table class="service-table">
        <thead>
            <tr>
                <th>Description</th>
                <th class="amount">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <div class="service-name">{{ $booking->service?->name ?? 'Service' }}</div>
                    @if($booking->service?->category?->name)
                        <div class="service-category">{{ $booking->service->category->name }}</div>
                    @endif
                    @if($booking->customer_address)
                        <div class="service-address">{{ $booking->customer_address }}</div>
                    @endif
                </td>
                <td class="amount">${{ number_format($booking->price, 2) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- ===== TOTALS ===== --}}
    <table class="totals-table">
        <tr class="subtotal-row">
            <td class="label">Subtotal</td>
            <td class="value">${{ number_format($booking->price, 2) }}</td>
        </tr>
        @if($booking->discount > 0)
        <tr class="discount-row">
            <td class="label">Discount</td>
            <td class="value">-${{ number_format($booking->discount, 2) }}</td>
        </tr>
        @endif
        @if($booking->tax > 0)
        <tr>
            <td class="label">Tax</td>
            <td class="value">${{ number_format($booking->tax, 2) }}</td>
        </tr>
        @endif
        <tr class="total-row">
            <td class="label">Total</td>
            <td class="value">${{ number_format($booking->total_amount, 2) }}</td>
        </tr>
    </table>

    {{-- ===== PAYMENT STATUS ===== --}}
    <table class="payment-table">
        <tr>
            <td style="width: 50%;">
                <div class="payment-label">Payment Status</div>
                <div class="payment-value">{{ ucfirst($booking->payment_status ?? 'N/A') }}</div>
            </td>
            <td style="width: 50%; text-align: right;">
                <div class="payment-label">Payment Method</div>
                <div class="payment-value">{{ ucfirst(str_replace('_', ' ', $booking->payment_method ?? 'N/A')) }}</div>
            </td>
        </tr>
    </table>

    {{-- ===== RATING ===== --}}
    @if($booking->rating)
    <div class="rating-box">
        <span class="rating-stars">
            @for($i = 1; $i <= 5; $i++)
                @if($i <= $booking->rating)&#9733;@else&#9734;@endif
            @endfor
        </span>
        <span class="rating-score">&nbsp; {{ $booking->rating }}/5</span>
        @if($booking->review)
            <div class="rating-text">"{{ $booking->review }}"</div>
        @endif
    </div>
    @endif

    {{-- ===== NOTES ===== --}}
    @php
        $hasNotes = false;
        $customerNotes = $booking->customer_notes;
        $providerNotes = $booking->provider_notes;

        // If customer_notes looks like JSON items array, format it
        if ($customerNotes) {
            $decoded = json_decode($customerNotes, true);
            if (is_array($decoded)) {
                $formatted = collect($decoded)->map(function ($item) {
                    $name = $item['name'] ?? $item['title'] ?? $item['service'] ?? 'Item';
                    $qty = $item['quantity'] ?? $item['qty'] ?? 1;
                    return "{$name} (x{$qty})";
                })->implode(', ');
                $customerNotes = $formatted;
            }
            $hasNotes = true;
        }
        if ($providerNotes) {
            $hasNotes = true;
        }
    @endphp

    @if($hasNotes)
    <div class="notes-box">
        @if($customerNotes)
            <div class="notes-title">Customer Notes / Items</div>
            <div class="notes-content">{{ $customerNotes }}</div>
        @endif
        @if($providerNotes)
            <div class="notes-title" style="margin-top: 10px;">Provider Notes</div>
            <div class="notes-content">{{ $providerNotes }}</div>
        @endif
    </div>
    @endif

    {{-- ===== TIMELINE ===== --}}
    @if($events->count() > 0)
    <div class="timeline-title">Booking Timeline</div>
    <table class="timeline-table">
        @foreach($events as $event)
        <tr>
            <td style="width: 20px; text-align: center;">
                <span class="timeline-dot {{ $loop->first ? 'dot-green' : 'dot-gray' }}">&nbsp;</span>
            </td>
            <td>
                <span class="timeline-event">{{ $event->description }}</span>
            </td>
            <td style="width: 140px; text-align: right;">
                <div class="timeline-time">{{ $event->created_at->format('M j, Y g:i A') }}</div>
                <div class="timeline-by">{{ $event->performed_by }}</div>
            </td>
        </tr>
        @endforeach
    </table>
    @endif

    {{-- ===== FOOTER ===== --}}
    <div class="footer">
        <div class="footer-brand">PUMP</div>
        <div class="footer-text">Thank you for choosing PUMP &mdash; Professional Maintenance Services</div>
        <div class="footer-text">Generated on {{ now()->format('M j, Y g:i A') }}</div>
    </div>

</body>
</html>

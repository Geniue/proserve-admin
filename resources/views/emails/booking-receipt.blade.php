<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, Helvetica, sans-serif; color: #333; line-height: 1.6; margin: 0; padding: 0; background: #f4f4f5; }
        .wrapper { max-width: 600px; margin: 0 auto; padding: 20px; }
        .card { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); padding: 32px; text-align: center; color: #fff; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 700; }
        .header p { margin: 8px 0 0; font-size: 14px; opacity: 0.9; }
        .body { padding: 32px; }
        .greeting { font-size: 16px; margin-bottom: 16px; }
        .summary { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .summary-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 14px; }
        .summary-row.total { border-top: 2px solid #e2e8f0; margin-top: 10px; padding-top: 12px; font-weight: 700; font-size: 18px; color: #2563eb; }
        .label { color: #64748b; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; }
        .badge-success { background: #dcfce7; color: #166534; }
        .footer { text-align: center; padding: 24px; font-size: 12px; color: #94a3b8; }
        .footer a { color: #2563eb; text-decoration: none; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="header">
                <h1>PUMP</h1>
                <p>Booking Receipt</p>
            </div>
            <div class="body">
                <p class="greeting">Hi {{ $booking->customer?->name ?? 'Customer' }},</p>
                <p>Here is your receipt for booking <strong>#{{ $booking->booking_number }}</strong>.</p>

                <div class="summary">
                    <div class="summary-row">
                        <span class="label">Service</span>
                        <span>{{ $booking->service?->name ?? 'N/A' }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="label">Provider</span>
                        <span>{{ $booking->provider?->user?->name ?? 'N/A' }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="label">Date</span>
                        <span>{{ $booking->scheduled_at?->format('M j, Y g:i A') ?? 'N/A' }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="label">Status</span>
                        <span class="badge badge-success">{{ ucfirst($booking->status) }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="label">Subtotal</span>
                        <span>${{ number_format($booking->price, 2) }}</span>
                    </div>
                    @if($booking->discount > 0)
                    <div class="summary-row">
                        <span class="label">Discount</span>
                        <span style="color: #16a34a;">-${{ number_format($booking->discount, 2) }}</span>
                    </div>
                    @endif
                    @if($booking->tax > 0)
                    <div class="summary-row">
                        <span class="label">Tax</span>
                        <span>${{ number_format($booking->tax, 2) }}</span>
                    </div>
                    @endif
                    <div class="summary-row total">
                        <span>Total</span>
                        <span>${{ number_format($booking->total_amount, 2) }}</span>
                    </div>
                </div>

                <p style="font-size: 14px; color: #64748b;">A PDF copy of this receipt is attached to this email.</p>
            </div>
        </div>
        <div class="footer">
            <p>PUMP — Professional Maintenance Services</p>
            <p>This is an automated email. Please do not reply.</p>
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoiceNumber }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 12px; }
        .container { width: 100%; padding: 20px; }
        .header-table { width: 100%; border: 0; margin: 0 0 16px 0; border-collapse: collapse; }
        .header-table td { border: 0; vertical-align: top; padding: 0; }
        .logo-box { width: 62px; height: 62px; border: 1px solid #f1d5dc; border-radius: 12px; overflow: hidden; background: #fff7fa; text-align: center; }
        .logo-box img { width: 100%; height: 100%; object-fit: cover; }
        .logo-fallback { line-height: 62px; font-size: 22px; font-weight: bold; color: #9f1239; }
        .brand-title { font-size: 16px; font-weight: bold; color: #881337; margin-bottom: 3px; }
        .brand-sub { color: #6b7280; font-size: 11px; }
        .invoice-title { font-size: 28px; font-weight: bold; color: #9f1239; text-align: right; margin-bottom: 6px; }
        .invoice-meta { font-size: 11px; color: #6b7280; text-align: right; }
        .section { margin-top: 14px; }
        .grid { width: 100%; border-collapse: collapse; }
        .grid td, .grid th { border: 1px solid #e5e7eb; padding: 8px 10px; }
        .grid th { background: #fff1f2; text-align: left; color: #4b5563; font-size: 11px; text-transform: uppercase; }
        .amount { text-align: right; white-space: nowrap; }
        .summary { margin-top: 10px; width: 45%; margin-left: auto; border-collapse: collapse; }
        .summary td { border: 1px solid #e5e7eb; padding: 8px 10px; }
        .summary .label { background: #f9fafb; color: #4b5563; }
        .summary .strong { font-weight: bold; }
        .summary .total-row td { background: #fff1f2; font-weight: bold; }
        .summary .remaining td { background: #fdf2f8; font-weight: bold; color: #9f1239; }
        .pay-card { margin-top: 14px; border: 1px solid #e5e7eb; border-radius: 12px; padding: 12px; background: #fafafa; }
        .pay-title { font-size: 12px; font-weight: bold; color: #374151; margin-bottom: 8px; }
        .pay-line { margin-bottom: 4px; color: #374151; }
        .pay-method { margin-top: 8px; border: 1px solid #e5e7eb; border-radius: 10px; padding: 10px; page-break-inside: avoid; }
        .pay-method:first-of-type { border-color: #fda4af; background: #fff1f2; }
        .pay-method-table { width: 100%; border-collapse: collapse; }
        .pay-method-table td { border: 0; padding: 0; vertical-align: top; }
        .pay-badge { display: inline-block; margin-left: 5px; border-radius: 10px; padding: 2px 6px; background: #ffe4e6; color: #9f1239; font-size: 9px; font-weight: bold; }
        .pay-qr { width: 92px; height: 92px; object-fit: contain; border: 1px solid #e5e7eb; border-radius: 8px; padding: 4px; background: #ffffff; }
        .small { color: #6b7280; font-size: 10px; margin-top: 14px; }
    </style>
</head>
<body>
    @php
        $tenant = $booking->tenant;
        $items = $booking->bookingItems;
        $payment = $booking->payment;

        $rawSubtotal = (float) $items->sum('subtotal');
        $transportFee = max(0, (float) ($booking->transport_fee ?? 0));
        $subtotal = $rawSubtotal > 0 ? ($rawSubtotal + $transportFee) : ((float) ($booking->service->price ?? 0) + $transportFee);
        $discount = max(0, (float) ($payment?->discount_amount ?? 0));
        if ($discount > $subtotal) {
            $discount = $subtotal;
        }

        $total = (float) ($payment?->amount ?? max(0, $subtotal - $discount));
        $dp = max(0, (float) ($payment?->dp_amount ?? 0));
        $paid = max(0, (float) ($payment?->paid_amount ?? 0));
        $remaining = max(0, $total - $paid);

        $tenantName = $tenant?->studio_name ?: $tenant?->name ?: 'LYNERA';
        $tenantLocation = $tenant?->studio_location ?: '-';
        $tenantInitial = strtoupper(substr((string) $tenantName, 0, 1));
        $accounts = collect($tenantPaymentAccounts ?? []);
        if ($accounts->isEmpty() && empty($hasTenantPaymentAccounts) && $tenant) {
            $hasLegacy = filled($tenant->payment_bank_name) || filled($tenant->payment_account_number) || filled($tenant->payment_account_name);
            if ($hasLegacy) {
                $accounts = collect([
                    (object) [
                        'bank_name' => $tenant->payment_bank_name,
                        'type' => 'bank',
                        'account_number' => $tenant->payment_account_number,
                        'account_name' => $tenant->payment_account_name,
                        'contact' => $tenant->payment_contact,
                        'notes' => null,
                        'is_primary' => true,
                        'is_active' => true,
                        'qr_code_path' => null,
                    ],
                ]);
            }
        }
    @endphp

    <div class="container">
        <table class="header-table">
            <tr>
                <td style="width: 70px;">
                    <div class="logo-box">
                        @if(!empty($tenantLogoDataUri))
                            <img src="{{ $tenantLogoDataUri }}" alt="Logo">
                        @else
                            <div class="logo-fallback">{{ $tenantInitial }}</div>
                        @endif
                    </div>
                </td>
                <td style="padding-left: 12px;">
                    <div class="brand-title">{{ $tenantName }}</div>
                    <div class="brand-sub">{{ $tenantLocation }}</div>
                    <div class="brand-sub">{{ $tenant?->email ?: '-' }}</div>
                </td>
                <td>
                    <div class="invoice-title">INVOICE</div>
                    <div class="invoice-meta">No: {{ $invoiceNumber }}</div>
                    <div class="invoice-meta">Tanggal: {{ now()->format('d M Y') }}</div>
                </td>
            </tr>
        </table>

        <table class="grid section">
            <tr>
                <th style="width: 30%;">Tagihan Kepada</th>
                <td>{{ $booking->customer->name }}</td>
            </tr>
            <tr>
                <th>Jadwal Layanan</th>
                <td>{{ $booking->booking_date?->format('d M Y') }} {{ substr((string) $booking->booking_time, 0, 5) }}</td>
            </tr>
            <tr>
                <th>Status Booking</th>
                <td>{{ ucfirst($booking->status) }}</td>
            </tr>
            <tr>
                <th>Status Pembayaran</th>
                <td>
                    @if($remaining <= 0)
                        Lunas
                    @elseif($paid > 0)
                        DP diterima, menunggu pelunasan
                    @else
                        Menunggu DP
                    @endif
                </td>
            </tr>
        </table>

        <table class="grid section">
            <thead>
                <tr>
                    <th>Layanan</th>
                    <th style="width: 12%;">Qty</th>
                    <th style="width: 18%;" class="amount">Harga</th>
                    <th style="width: 20%;" class="amount">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @if($items->isNotEmpty())
                    @foreach($items as $item)
                        <tr>
                            <td>{{ $item->service->name }}</td>
                            <td>{{ $item->people_count }} org</td>
                            <td class="amount">Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}</td>
                            <td class="amount">Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td>{{ $booking->service->name }}</td>
                        <td>{{ $booking->total_people ?? 1 }} org</td>
                        <td class="amount">Rp {{ number_format((float) $booking->service->price, 0, ',', '.') }}</td>
                        <td class="amount">Rp {{ number_format((float) $booking->service->price, 0, ',', '.') }}</td>
                    </tr>
                @endif
                @if($transportFee > 0)
                    <tr>
                        <td>Biaya Transportasi</td>
                        <td>1</td>
                        <td class="amount">Rp {{ number_format($transportFee, 0, ',', '.') }}</td>
                        <td class="amount">Rp {{ number_format($transportFee, 0, ',', '.') }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <table class="summary">
            <tr>
                <td class="label">Subtotal</td>
                <td class="amount">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label">Discount</td>
                <td class="amount">- Rp {{ number_format($discount, 0, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <td>Total Tagihan</td>
                <td class="amount">Rp {{ number_format($total, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label">DP Minimum</td>
                <td class="amount">Rp {{ number_format($dp, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label strong">Sudah Dibayar</td>
                <td class="amount strong">Rp {{ number_format($paid, 0, ',', '.') }}</td>
            </tr>
            <tr class="remaining">
                <td>Sisa Pembayaran</td>
                <td class="amount">Rp {{ number_format($remaining, 0, ',', '.') }}</td>
            </tr>
        </table>

        <div class="pay-card">
            <div class="pay-title">Informasi Pembayaran</div>
            @forelse($accounts as $account)
                @php
                    $typeLabel = method_exists($account, 'typeLabel') ? $account->typeLabel() : 'Bank Transfer';
                    $qrDataUri = $tenantPaymentQrDataUris[$account->id ?? 0] ?? null;
                @endphp
                <div class="pay-method">
                    <table class="pay-method-table">
                        <tr>
                            <td>
                                <div class="pay-line">
                                    <strong>{{ $account->bank_name ?: '-' }}</strong>
                                    <span class="pay-badge">{{ $typeLabel }}</span>
                                    @if(!empty($account->is_primary))<span class="pay-badge">Utama</span>@endif
                                </div>
                                @if(!empty($account->account_number))<div class="pay-line">Nomor: {{ $account->account_number }}</div>@endif
                                @if(!empty($account->account_name))<div class="pay-line">Atas Nama: {{ $account->account_name }}</div>@endif
                                @if(!empty($account->contact))<div class="pay-line">Kontak Konfirmasi: {{ $account->contact }}</div>@endif
                                @if(!empty($account->notes))<div class="pay-line">Catatan: {{ $account->notes }}</div>@endif
                            </td>
                            @if($qrDataUri)
                                <td style="width:105px; text-align:right;"><img src="{{ $qrDataUri }}" alt="QRIS" class="pay-qr"></td>
                            @endif
                        </tr>
                    </table>
                </div>
            @empty
                <div class="pay-line">Belum ada metode pembayaran aktif yang diset tenant.</div>
            @endforelse
            @if(!empty($tenant?->payment_instructions))
                <div class="pay-line" style="margin-top:8px;">Instruksi: {{ $tenant->payment_instructions }}</div>
            @endif
        </div>

        <div class="small">
            Invoice ini dibuat otomatis oleh sistem pada {{ now()->format('d M Y H:i') }}.
        </div>
    </div>
</body>
</html>

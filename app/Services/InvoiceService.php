<?php

namespace App\Services;

use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

class InvoiceService
{
    public function downloadBookingInvoice(Booking $booking): Response
    {
        [$pdf, $invoiceNumber] = $this->buildPdf($booking);

        return $pdf->download($invoiceNumber.'.pdf');
    }

    public function previewBookingInvoice(Booking $booking): Response
    {
        [$pdf, $invoiceNumber] = $this->buildPdf($booking);

        return $pdf->stream($invoiceNumber.'.pdf');
    }

    /**
     * @return array{0:mixed,1:string}
     */
    private function buildPdf(Booking $booking): array
    {
        $invoiceNumber = sprintf('INV-%s-%04d', now()->format('Ymd'), $booking->id);
        $booking = $booking->loadMissing([
            'customer',
            'service',
            'payment',
            'bookingItems.service',
            'tenant.paymentAccounts',
        ]);
        $allPaymentAccounts = $booking->tenant?->paymentAccounts ?? collect();
        $activePaymentAccounts = $allPaymentAccounts
            ->where('is_active', true)
            ->values();
        $qrDataUris = $activePaymentAccounts
            ->mapWithKeys(fn ($account): array => [
                $account->id => $this->publicImageDataUri($account->qr_code_path),
            ])
            ->filter()
            ->all();

        $pdf = Pdf::loadView('admin.invoices.booking', [
            'invoiceNumber' => $invoiceNumber,
            'booking' => $booking,
            'tenantLogoDataUri' => $this->tenantLogoDataUri($booking),
            'tenantPaymentAccounts' => $activePaymentAccounts,
            'hasTenantPaymentAccounts' => $allPaymentAccounts->isNotEmpty(),
            'tenantPaymentQrDataUris' => $qrDataUris,
        ]);

        return [$pdf, $invoiceNumber];
    }

    private function tenantLogoDataUri(Booking $booking): ?string
    {
        return $this->publicImageDataUri($booking->tenant?->logo_path);
    }

    private function publicImageDataUri(?string $path): ?string
    {
        $normalizedPath = trim((string) $path);
        if ($normalizedPath === '') {
            return null;
        }

        $absolutePath = storage_path('app/public/'.$normalizedPath);
        if (! File::exists($absolutePath)) {
            return null;
        }

        $mimeType = File::mimeType($absolutePath) ?: 'image/png';
        $binary = File::get($absolutePath);

        return 'data:'.$mimeType.';base64,'.base64_encode($binary);
    }
}

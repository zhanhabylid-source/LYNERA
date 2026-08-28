<?php

namespace App\Console\Commands;

use App\Services\Payments\PaymentService;
use Illuminate\Console\Command;

class AutoSettlePassedServiceBookings extends Command
{
    protected $signature = 'payments:auto-settle-past-service';

    protected $description = 'Tandai otomatis pembayaran booking menjadi lunas jika tanggal layanan sudah lewat (kecuali booking dibatalkan).';

    public function __construct(
        private readonly PaymentService $paymentService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $count = $this->paymentService->autoSettlePassedServiceBookings();

        $this->info("Auto-settle selesai. Total pembayaran yang ditandai lunas: {$count}.");

        return self::SUCCESS;
    }
}

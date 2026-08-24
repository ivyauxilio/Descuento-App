<?php

namespace App\Console\Commands;

use App\Models\PhysicalCard;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GeneratePhysicalCards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // protected $signature = 'app:generate-physical-cards';
    protected $signature = 'cards:generate 
                            {count=100 : Number of cards to generate}
                            {--prefix=DSC : Card prefix}
                            {--with-qr : Generate QR codes}
                            {--balance=0 : Initial balance for cards}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate physical cards with QR codes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = $this->argument('count');
        $prefix = $this->option('prefix');
        $withQR = $this->option('with-qr');
        $initialBalance = (int) $this->option('balance');

        $this->info("Generating {$count} cards...");
        $this->info("Prefix: {$prefix}, Initial Balance: ₱{$initialBalance}");
        
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $generated = 0;

        for ($i = 0; $i < $count; $i++) {
            try {
                $cardNumber = $this->generateCardNumber($prefix);
                $cardUid = $this->generateCardUid();
                $qrData = $this->generateQrData($cardNumber);
                
                $card = PhysicalCard::create([
                    'card_id' => Str::uuid(),
                    'card_number' => $cardNumber,
                    'card_uid' => $cardUid,
                    'qr_code' => $qrData,
                    'status' => 'inactive',
                    'balance' => $initialBalance,
                    'points' => 0,
                ]);

                // Generate QR code image
                if ($withQR) {
                    $this->generateQRImage($qrData, $cardNumber);
                }

                $generated++;
                $bar->advance();

            } catch (\Exception $e) {
                $this->error("\nError generating card: " . $e->getMessage());
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ Successfully generated {$generated} cards!");

        // Show sample cards
        $this->table(
            ['Card Number', 'QR Code', 'Status', 'Balance'],
            PhysicalCard::latest()->take(5)->get(['card_number', 'qr_code', 'status', 'balance'])->toArray()
        );
    }

    private function generateCardNumber(string $prefix): string
    {
        $number = $prefix . str_pad(random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
        $checksum = $this->calculateLuhn($number);
        return $number . $checksum;
    }

    private function calculateLuhn(string $number): int
    {
        $sum = 0;
        $alt = false;
        
        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $digit = intval($number[$i]);
            if ($alt) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $sum += $digit;
            $alt = !$alt;
        }
        
        return (10 - ($sum % 10)) % 10;
    }

    private function generateCardUid(): string
    {
        return strtoupper(bin2hex(random_bytes(8)));
    }

    private function generateQrData(string $cardNumber): string
    {
        // return 'DSC-CARD-' . $cardNumber . '-' . strtoupper(Str::random(8));
        return strtoupper(Str::random(8));
    }

    private function generateQRImage(string $qrData, string $cardNumber): void
    {
        // $path = storage_path('app/public/cards');
        // if (!file_exists($path)) {
        //     mkdir($path, 0777, true);
        // }

        // $qrPath = $path . '/' . $cardNumber . '.png';
        
        // QrCode::format('png')
        //     ->size(300)
        //     ->errorCorrection('H')
        //     ->generate($qrData, $qrPath);
                try {
            // Create directory if it doesn't exist
            $path = storage_path('app/public/cards');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            // Generate QR code as SVG (no Imagick needed)
            $svg = QrCode::size(300)
                ->format('svg')
                ->errorCorrection('H')
                ->generate($qrData);

            // Save as SVG (no need for Imagick)
            $svgPath = $path . '/' . $cardNumber . '.svg';
            file_put_contents($svgPath, $svg);

            // Optionally convert SVG to PNG using GD
            $this->convertSvgToPng($svgPath, $path . '/' . $cardNumber . '.png');

        } catch (\Exception $e) {
            $this->warn("Could not generate QR image: " . $e->getMessage());
        }
    }
}
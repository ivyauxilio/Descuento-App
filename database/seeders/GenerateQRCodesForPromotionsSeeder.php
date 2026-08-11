<?php

namespace Database\Seeders;

use App\Models\Promotion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GenerateQRCodesForPromotionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Generating QR codes for promotions...');

        // Get all promotions without QR codes
        $promotions = Promotion::whereNull('qr_code')->get();

        if ($promotions->isEmpty()) {
            $this->command->info('All promotions already have QR codes!');
            return;
        }

        $bar = $this->command->getOutput()->createProgressBar($promotions->count());
        $bar->start();

        $generated = 0;

        foreach ($promotions as $promotion) {
            // Generate unique QR code
            $qrCode = $this->generateUniqueQrCode();
            
            // Update the promotion
            $promotion->update([
                'qr_code' => $qrCode,
            ]);

            $generated++;
            $bar->advance();
        }

        $bar->finish();

        $this->command->newLine();
        $this->command->info("✅ Generated QR codes for {$generated} promotions!");
    }

    /**
     * Generate a unique QR code.
     */
    private function generateUniqueQrCode(): string
    {
        $prefix = 'PROMO';
        $timestamp = now()->timestamp;
        $random = strtoupper(Str::random(8));
        
        $qrCode = $prefix . '-' . $timestamp . '-' . $random;

        // Ensure uniqueness
        while (Promotion::where('qr_code', $qrCode)->exists()) {
            $random = strtoupper(Str::random(8));
            $qrCode = $prefix . '-' . $timestamp . '-' . $random;
        }

        return $qrCode;
    }
}
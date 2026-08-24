<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PhysicalCard extends Model
{
    use SoftDeletes;

    protected $table = 'physical_cards';
    protected $primaryKey = 'card_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'card_id',
        'user_id',
        'card_number',
        'card_uid',
        'qr_code',
        'status',
        'issued_at',
        'expires_at',
        'balance',
        'points',
    ];

    protected $casts = [
        'balance' => 'integer',
        'points' => 'integer',
        'issued_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->card_id)) {
                $model->card_id = (string) Str::uuid();
            }
            if (empty($model->card_number)) {
                $model->card_number = $model->generateCardNumber();
            }
            if (empty($model->card_uid)) {
                $model->card_uid = $model->generateCardUid();
            }
            if (empty($model->qr_code)) {
                $model->qr_code = $model->generateQrCodeToken();
            }
        });
    }

   // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function redemptions()
    {
        return $this->hasMany(QrCodeUsage::class, 'card_id', 'card_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Helper Methods
    public function isActive(): bool
    {
        return $this->status === 'active' && 
               ($this->expires_at === null || $this->expires_at > now());
    }

    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at < now();
    }

    public function activate($userId): bool
    {
        if ($this->isActive()) {
            return false;
        }

        $this->user_id = $userId;
        $this->status = 'active';
        $this->issued_at = now();
        $this->expires_at = now()->addYears(2);
        $this->save();

        return true;
    }

    public function redeem($amount): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        if ($this->balance < $amount) {
            return false;
        }

        $this->balance -= $amount;
        $this->save();

        return true;
    }

    public function addPoints($points): void
    {
        $this->points += $points;
        $this->save();
    }

    public function getStatusLabel(): string
    {
        return ucfirst($this->status);
    }

    public function getStatusColor(): string
    {
        return [
            'active' => 'success',
            'inactive' => 'secondary',
            'lost' => 'danger',
            'expired' => 'warning',
        ][$this->status] ?? 'secondary';
    }

    /**
     * Generate a unique card number (printed on card).
     */
    public function generateCardNumber(): string
    {
        $prefix = 'DSC';
        $random = str_pad(random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
        $checkDigit = $this->calculateCheckDigit($prefix . $random);
        
        return $prefix . $random . $checkDigit;
    }

    /**
     * Generate a unique card UID (for RFID/NFC chip).
     */
    public function generateCardUid(): string
    {
        return strtoupper(bin2hex(random_bytes(8)));
    }

    /**
     * Generate a QR code token.
     */
    public function generateQrCodeToken(): string
    {
        // Token format: CARD-{timestamp}-{random}
        return 'CARD-' . now()->timestamp . '-' . strtoupper(Str::random(8));
    }

    /**
     * Generate QR code image for printing.
     */
    public function getQrCodeImage(): string
    {
        $data = json_encode([
            'card_uid' => $this->card_uid,
            'card_number' => $this->card_number,
            'token' => $this->qr_code,
        ]);

        return QrCode::size(300)
            ->format('svg')
            ->errorCorrection('H')
            ->generate($data);
    }

    /**
     * Calculate Luhn check digit.
     */
    private function calculateCheckDigit($number): int
    {
        $sum = 0;
        $alt = false;
        
        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $digit = intval($number[$i]);
            
            if ($alt) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit = $digit - 9;
                }
            }
            
            $sum += $digit;
            $alt = !$alt;
        }
        
        return (10 - ($sum % 10)) % 10;
    }

    /**
     * Mask card number for display.
     */
    public function getMaskedCardNumberAttribute(): string
    {
        $length = strlen($this->card_number);
        if ($length <= 4) {
            return $this->card_number;
        }
        
        $visible = 4;
        $masked = str_repeat('*', $length - $visible);
        $lastDigits = substr($this->card_number, -$visible);
        
        return $masked . $lastDigits;
    }

    public function getMaskedNumber(): string
    {
        $length = strlen($this->card_number);
        if ($length <= 4) {
            return $this->card_number;
        }
        return substr($this->card_number, 0, 4) . '****' . substr($this->card_number, -4);
    }

}
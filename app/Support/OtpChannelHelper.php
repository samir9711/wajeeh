<?php

namespace App\Support;

use App\Models\Otp;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\App;

class OtpChannelHelper
{
    public static int $ttlMinutes = 10;

    public static function generate(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    public static function send($model, string $purpose, string $channel): void
    {

        Otp::where([
            'verifiable_type' => get_class($model),
            'verifiable_id'   => $model->id,
            'purpose'         => $purpose,
            'is_used'         => false,
        ])->delete();


        $code = self::generate();
        //$code='123456';
        Otp::create([
            'verifiable_type' => get_class($model),
            'verifiable_id'   => $model->id,
            'otp'             => $code,
            'purpose'         => $purpose,
            'channel'         => $channel,
            'expires_at'      => now()->addMinutes(self::$ttlMinutes),
        ]);


        $originalLocale = App::getLocale();
        $lang = $model->language ?? $originalLocale;
        App::setLocale($lang);

        // --- قنوات الإرسال ---
        switch ($channel) {
            case 'email':
                if (!empty($model->email)) {

                    Mail::to($model->email)->send(new OtpMail($model, $code, $purpose, $lang));
                }
                break;

            case 'sms':
                // SmsProvider::send($model->prefix_phone.$model->phone_number, "Your OTP is: {$code}");
                break;

            case 'whatsapp':
                // WhatsAppProvider::send($model->prefix_phone.$model->phone_number, "Your OTP is: {$code}");
                break;
        }

        App::setLocale($originalLocale);
    }

    public static function verify($model, string $purpose, string $otp, ?string $channel = null): bool
    {
        $record = Otp::where([
            'verifiable_type' => get_class($model),
            'verifiable_id'   => $model->id,
            'purpose'         => $purpose,
            'is_used'         => false,
        ])
            ->when($channel, fn($q) => $q->where('channel', $channel))
            ->latest('id')
            ->first();

        if (!$record) return false;
        if ($record->expires_at && now()->greaterThan($record->expires_at)) return false;
        if (!hash_equals($record->otp, $otp)) return false;

        $record->update(['is_used' => true, 'verified_at' => now()]);

        return true;
    }
}

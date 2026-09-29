<?php

namespace App\Services\Functional;

use App\Http\Traits\GeneralTrait;
use App\Models\Admin;
use App\Models\User;
use App\Support\OtpChannelHelper;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Unified reset password service:
 * - sendResetOtp(actor,user/admin)
 * - verifyResetOtp(actor,user/admin) => reset-token
 * - resetPassword(authenticated by reset-token)
 */
class ResetPasswordService
{
    use GeneralTrait;

    /** Resolve target model & allowed channel according to actor */
    protected function resolveTarget(Request $request): array
    {
        $actor = $request->input('actor'); // 'user' | 'admin'

        if ($actor === 'admin') {
            $admin = Admin::where('email', $request->input('email'))->first();
            if (!$admin) {
                throw new HttpResponseException($this->requiredField(__('validation.exists', ['attribute' => 'email'])));
            }
            return [$actor, $admin, 'email'];
        }

        // user:
        if ($request->filled('email')) {
            $user = User::where('email', $request->input('email'))->first();
            if (!$user) {
                throw new HttpResponseException($this->requiredField(__('validation.exists', ['attribute' => 'email'])));
            }
            return [$actor, $user, 'email'];
        }

        $user = User::where('prefix_phone', $request->input('prefix_phone'))
            ->where('phone_number', $request->input('phone_number'))
            ->first();

        if (!$user) {
            throw new HttpResponseException($this->requiredField(__('validation.exists', ['attribute' => 'phone'])));
        }

        return [$actor, $user, $request->input('channel')];
    }

    /** Send reset OTP (user/admin) */
    public function sendResetOtp(Request $request): array
    {
        [$actor, $model, $channel] = $this->resolveTarget($request);

        if ($actor === 'admin' && $channel !== 'email') {
            throw new HttpResponseException($this->requiredField(__('validation.custom.channel.admin_email_only')));
        }

        if (in_array($channel, ['sms','whatsapp'])) {
            if (empty($model->prefix_phone) || empty($model->phone_number)) {
                throw new HttpResponseException($this->requiredField(__('validation.custom.phone.required')));
            }
        } elseif ($channel === 'email' && empty($model->email)) {
            throw new HttpResponseException($this->requiredField(__('validation.custom.email.required')));
        }

        OtpChannelHelper::send($model, 'reset_password', $channel);

        return ['message' => __('messages.otp_sent')];
    }

    /** Verify reset OTP => issue reset-password token (Sanctum) */
    public function verifyResetOtp(Request $request): array
    {
        $actor = $request->input('actor');

        if ($actor === 'admin') {
            $admin = Admin::where('email', $request->input('email'))->firstOrFail();
            $ok = OtpChannelHelper::verify($admin, 'reset_password', $request->input('otp'), 'email');
            if (!$ok) {
                throw new HttpResponseException($this->requiredField(__('messages.invalid_otp')));
            }
            $token = $admin->createToken('admin-reset-password-token')->plainTextToken;

            return [
                'token' => $token,
                'actor' => 'admin',
                'admin' => ['id' => $admin->id, 'email' => $admin->email],
            ];
        }

        // user:
        if ($request->filled('email')) {
            $user = User::where('email', $request->input('email'))->firstOrFail();
            $channel = 'email';
        } else {
            $user = User::where('prefix_phone', $request->input('prefix_phone'))
                ->where('phone_number', $request->input('phone_number'))
                ->firstOrFail();
            $channel = $request->input('channel', $user->otp_delivery_method ?? null);
        }

        $ok = OtpChannelHelper::verify($user, 'reset_password', $request->input('otp'), $channel);
        if (!$ok) {
            throw new HttpResponseException($this->requiredField(__('messages.invalid_otp')));
        }

        $token = $user->createToken('user-reset-password-token')->plainTextToken;

        return [
            'token' => $token,
            'actor' => 'user',
            'user'  => ['id' => $user->id, 'email' => $user->email],
        ];
    }

    /** Set new password (authenticated with reset-token) */
    public function resetPassword(Request $request): array
    {
        $authModel = $request->user();
        if (!$authModel) {
            throw new HttpResponseException($this->requiredField(__('messages.unauthenticated')));
        }

        $authModel->password = Hash::make($request->input('password'));
        $authModel->save();

        return ['message' => __('messages.password_updated')];
    }
}

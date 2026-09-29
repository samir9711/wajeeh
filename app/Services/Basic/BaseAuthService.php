<?php

namespace App\Services\Basic;

use App\Facades\Services\Auth\UserAuthFacade;
use App\Http\Requests\Basic\BasicRequest;
use App\Http\Traits\GeneralTrait;
use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Support\OtpChannelHelper;
use Illuminate\Database\Eloquent\Model;

abstract class BaseAuthService
{
    use GeneralTrait;

    /** @var class-string<\Illuminate\Database\Eloquent\Model> */
    protected $model;
    protected string $key;
    /** @var class-string */
    protected $resource;
    protected string $guard;

    abstract protected function setVariables(): void;

    public function __construct()
    {
        $this->setVariables();
    }

    protected function issueToken(Model $model, ?string $deviceToken = null, array $deviceMeta = []): array
    {
        $accessToken = $model->createToken('access-token');
        $token       = $accessToken->plainTextToken;
        $tokenId     = $accessToken->accessToken->id ?? null;

        if ($deviceToken) {
            $this->syncDeviceToken($model, $deviceToken, $deviceMeta);
        }

        return [$token, $tokenId];
    }
    protected function syncDeviceToken(Model $model, string $deviceToken, array $deviceMeta = []): DeviceToken
    {
        $deviceId = $deviceMeta['device_id'] ?? null;

        if ($deviceId) {
            DeviceToken::query()
                ->where('tokenable_type', get_class($model))
                ->where('tokenable_id', $model->id)
                ->where('device_id', $deviceId)
                ->where('token', '!=', $deviceToken)
                ->update([
                    'is_active'   => false,
                    'revoked_at'   => now(),
                    'updated_at'   => now(),
                ]);
        }

        return DeviceToken::updateOrCreate(
            ['token' => $deviceToken],
            [
                'tokenable_type' => get_class($model),
                'tokenable_id'   => $model->id,
                'platform'       => $deviceMeta['platform'] ?? 'unknown',
                'device_name'    => $deviceMeta['device_name'] ?? null,
                'device_id'      => $deviceId,
                'is_active'      => true,
                'last_seen_at'    => now(),
                'revoked_at'     => null,
                'meta'           => $deviceMeta ?: null,
            ]
        );
    }
    public function revokeDeviceToken(Request $request): void
    {
        $token = $request->input('token_device');

        if (! $token) {
            return;
        }

        DeviceToken::query()
            ->where('token', $token)
            ->update([
                'is_active'  => false,
                'revoked_at'  => now(),
                'updated_at'  => now(),
            ]);
    }
    public function syncLoggedInDeviceToken(Request $request): array
    {
        $actor = $request->user($this->guard);

        if (! $actor) {
            throw new HttpResponseException($this->unAuthorizeResponse());
        }

        $this->syncDeviceToken($actor, (string) $request->input('token_device'), [
            'platform'    => $request->input('platform', 'unknown'),
            'device_name' => $request->input('device_name'),
            'device_id'   => $request->input('device_id'),
        ]);

        return ['message' => __('messages.device_token_saved')];
    }

    /*
    public function revokeDeviceToken(?string $deviceToken): void
    {
        if (!$deviceToken) return;
        DeviceToken::where('token_device', $deviceToken)->delete();
    }
    */
    /* ===== User Register Flow ===== */
    public function register(BasicRequest $request): array
    {
        $in = $request->validated();

        // خليه يلتقط القناة من otp_delivery_method أو channel أو يخليها email كـ default
        $channel = $in['otp_delivery_method'] ?? $in['channel'] ?? 'email';

        // تنسيق رقم الهاتف (اختياري)
        if (!empty($in['phone_number'])) {
            $in['phone_number'] = preg_replace('/[\s-]+/', '', (string) $in['phone_number']);
        }

        // تحقّق من وجود مستخدم بنفس الإيميل أو الهاتف
        /*
        $user = $this->model::where(function ($query) use ($in) {
            $query->where(function ($q) use ($in) {
                $q->where('prefix_phone', $in['prefix_phone'] ?? null)
                    ->where('phone_number', $in['phone_number'] ?? null);
            })->orWhere('email', $in['email'] ?? null);
        })->first();
        */
        $userQuery = $this->model::query();
        if (!empty($in['email'])) {
            $userQuery->orWhere('email', $in['email']);
        }
        if (!empty($in['phone'])) {
            $userQuery->orWhere('phone', $in['phone']);
        }
        $user = $userQuery->first();
        if ($user) {
            // مهم: "إرجاع" وليس مجرد نداء من دون return
            return \App\Facades\Services\Auth\UserAuthFacade::checkUserStatus($user);
        }

        // خزّن قناة الـ OTP المختارة على الحساب
        $in['otp_delivery_method'] = $channel;

        // تشفير كلمة المرور
        $in['password'] = \Illuminate\Support\Facades\Hash::make($in['password']);

        /** @var \Illuminate\Database\Eloquent\Model $user */
        $user = $this->model::create($in);

        \App\Support\OtpChannelHelper::send($user, 'register', $channel);

        return [
            'message'     => __('messages.otp_sent'),
            $this->key    => $this->resource::make($user),
            'need_login'  => false,
            'need_verify' => true,
        ];
    }

    public function resendRegisterOtp(BasicRequest $request): array
    {
        $user = $this->model::findOrFail($request->input('id'));
        if (!$user->otp_delivery_method) {
            throw new HttpResponseException($this->requiredField(__('messages.channel_not_selected')));
        }
        OtpChannelHelper::send($user, 'register', $user->otp_delivery_method);
        return ['message' => __('messages.otp_resent')];
    }

    abstract public function verifyOtp(BasicRequest $request): array;

    /* ===== Login (User/Admin) ===== */
    abstract public function login(BasicRequest $request): array;

    /* ===== Reset Password (User/Admin) ===== */
    public function sendResetOtp(BasicRequest $request): array
    {
        $actor = $request->string('actor')->toString();

        if ($actor === 'admin') {
            $admin = $this->model::where('email', $request->input('email'))->first();
            if (!$admin) throw new HttpResponseException($this->requiredField(__('messages.account_not_found')));
            OtpChannelHelper::send($admin, 'reset', 'email');
            return ['message' => __('messages.otp_sent')];
        }

        // user
        $channel = $request->string('channel')->toString();
        $user = null;

        if ($request->filled('email')) {
            $user = $this->model::where('email', $request->input('email'))->first();
        } elseif ($request->filled('prefix_phone') && $request->filled('phone_number')) {
            $user = $this->model::where('prefix_phone', $request->input('prefix_phone'))
                ->where('phone_number', $request->input('phone_number'))
                ->first();
        }

        if (!$user) throw new HttpResponseException($this->requiredField(__('messages.account_not_found')));

        OtpChannelHelper::send($user, 'reset', $channel);
        return ['message' => __('messages.otp_sent')];
    }

    /*public function verifyResetOtp(Request $request): array
    {
        $actor = $request->string('actor')->toString();

        if ($actor === 'admin') {
            $admin = $this->model::where('email', $request->input('email'))->first();
            if (!$admin) throw new HttpResponseException($this->requiredField(__('messages.account_not_found')));

            $ok = OtpChannelHelper::verify($admin, 'reset', (string) $request->input('otp'), 'email');
            if (!$ok) throw new HttpResponseException($this->requiredField(__('messages.invalid_otp')));

            return ['message' => __('messages.otp_verified')];
        }

        $user = null;
        $channel = null;

        if ($request->filled('email')) {
            $user = $this->model::where('email', $request->input('email'))->first();
            $channel = $user->otp_delivery_method ?: 'sms';
        } elseif ($request->filled('prefix_phone') && $request->filled('phone_number')) {
            $user = $this->model::where('prefix_phone', $request->input('prefix_phone'))
                ->where('phone_number', $request->input('phone_number'))
                ->first();
            $channel = $user->otp_delivery_method ?: 'sms';
        }

        if (!$user) throw new HttpResponseException($this->requiredField(__('messages.account_not_found')));

        $ok = OtpChannelHelper::verify($user, 'reset', (string) $request->input('otp'), $channel);
        if (!$ok) throw new HttpResponseException($this->requiredField(__('messages.invalid_otp')));

        return ['message' => __('messages.otp_verified')];
    }*/

    public function resetPassword(BasicRequest $request): array
    {
        $actor = $request->string('actor')->toString();

        if ($actor === 'admin') {
            $admin = $this->model::where('email', $request->input('email'))->first();
            if (!$admin) throw new HttpResponseException($this->requiredField(__('messages.account_not_found')));
            $admin->update(['password' => Hash::make($request->input('password'))]);
            return ['message' => __('messages.password_updated')];
        }

        $user = null;
        if ($request->filled('email')) {
            $user = $this->model::where('email', $request->input('email'))->first();
        } elseif ($request->filled('prefix_phone') && $request->filled('phone_number')) {
            $user = $this->model::where('prefix_phone', $request->input('prefix_phone'))
                ->where('phone_number', $request->input('phone_number'))
                ->first();
        }
        if (!$user) throw new HttpResponseException($this->requiredField(__('messages.account_not_found')));

        $user->update(['password' => Hash::make($request->input('password'))]);

        return ['message' => __('messages.password_updated')];
    }

    /* ===== إضافات مطلوبة من FatherAuthController ===== */

    // إعادة إرسال OTP النهائي (نفس قناة المستخدم المخزّنة)
    /*public function resendFinalOtp(Request $request): array
    {
        if ($this->guard !== 'user') {
            // للأدمن ممكن تعتبرها غير متاحة
            return ['message' => __('messages.unavailable_operation')];
        }

        $user = $request->user('user');
        if (!$user) throw new HttpResponseException($this->unAuthorizeResponse());

        if (!$user->otp_delivery_method) {
            throw new HttpResponseException($this->requiredField(__('messages.channel_not_selected')));
        }

        OtpChannelHelper::send($user, 'register', $user->otp_delivery_method);
        return ['message' => __('messages.otp_resent')];
    }*/

    // طلب تغيير رقم الهاتف: نرسل OTP على القناة المطلوبة للتحقق
    public function requestPhoneChange(Request $request): array
    {
        $actorUser = $this->guard === 'admin' ? $request->user('admin') : $request->user('user');
        if (!$actorUser) throw new HttpResponseException($this->unAuthorizeResponse());

        $channel = $request->string('channel')->toString() ?: 'sms';
        // نرسل OTP لغرض phone_change على القناة المطلوبة
        OtpChannelHelper::send($actorUser, 'phone_change', $channel);

        return ['message' => __('messages.otp_sent')];
    }

    // تأكيد تغيير الهاتف بعد إدخال الـ OTP + الهاتف الجديد
    public function verifyPhoneChangeOtp(Request $request): array
    {
        $actorUser = $this->guard === 'admin' ? $request->user('admin') : $request->user('user');
        if (!$actorUser) throw new HttpResponseException($this->unAuthorizeResponse());

        $otp = (string) $request->input('otp');
        $channel = $actorUser->otp_delivery_method?? "email";

        $ok = OtpChannelHelper::verify($actorUser, 'phone_change', $otp, $channel);
        if (!$ok) throw new HttpResponseException($this->requiredField(__('messages.invalid_otp')));

        // لو تبغى التغيير لرقم جديد يأتي ضمن نفس الطلب:
        if ($request->filled('prefix_phone') && $request->filled('phone_number')) {
            $prefix_phone = $request->input('prefix_phone');
            $phone_number = preg_replace('/[\s-]+/', '', (string) $request->input('phone_number'));

            if($this->model::where(function ($query) use ($prefix_phone,$phone_number,$actorUser){

                $query->where("prefix_phone",$prefix_phone)
                      ->where("phone_number",$phone_number)
                      ->where("id","!=",$actorUser->id);

            })->exists())
                throw new HttpResponseException($this->requiredField(__('messages.invalid_phone_number')));

            $actorUser->update([
                'prefix_phone' => $prefix_phone,
                'phone_number' => $phone_number,
                'phone_status' => true,
                'phone_verified_at' => now(),
            ]);
        }

        return [
            'message' => __('messages.phone_updated'),
            'token' => null,
            $this->key => $this->resource::make($actorUser->fresh()),
            ];
    }

    // تحديث اللغة
    public function updateLanguage(Request $request): array
    {
        $actorUser = $this->guard === 'admin' ? $request->user('admin') : $request->user('user');
        if (!$actorUser) throw new HttpResponseException($this->unAuthorizeResponse());

        $lang = $request->string('language')->toString() ?: 'ar';
        $actorUser->update(['language' => $lang]);

        return [$this->key => $this->resource::make($actorUser->fresh())];
    }
}

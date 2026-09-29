<?php

namespace App\Services\Functional;

use App\Http\Requests\Basic\BasicRequest;
use App\Http\Resources\Model\UserResource;
use App\Models\User;
use App\Services\Basic\BaseAuthService;
use App\Support\OtpChannelHelper;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Hash;

class UserAuthService extends BaseAuthService
{
    protected function setVariables(): void
    {
        $this->model    = User::class;
        $this->key      = 'user';
        $this->resource = UserResource::class;
        $this->guard    = 'user';
    }

    public function checkUserStatus(object $user) : array
    {
        $channel = $user->otp_delivery_method?? "email";
        if($user->status)
                return [
                    'message' => __('messages.need_login'),
                    $this->key => $this->resource::make($user),
                    "need_login" => true,
                    "need_verify" => false,
                ];
        else
            {
                OtpChannelHelper::send($user, 'register', $channel);
                return [
                    'message' => __('messages.otp_sent'),
                    $this->key => $this->resource::make($user),
                    "need_login" => false,
                    "need_verify" => true,
                ];
            }
    }

    public function login(BasicRequest $request): array
    {
        $email  = $request->input('email');
        $pass   = $request->input('password');
        $pp     = $request->input('prefix_phone');
        $pn     = $request->input('phone_number');
        $social = $request->input('social_id');

        // user
        if ($social && !$pass && !$email && !$pp && !$pn) {
            $user = $this->model::where('social_id', $social)->first();
        }
        elseif ($email && $pass) {
            $user = $this->model::where('email', $email)->first();
        } elseif ($pp && $pn && $pass) {
            $user = $this->model::where('prefix_phone', $pp)->where('phone_number', $pn)->first();
        } else {
            throw new HttpResponseException($this->requiredField(__('messages.invalid_credentials')));
        }

        if (!$user || !$user->password || !Hash::check($pass, $user->password)) {
            throw new HttpResponseException($this->requiredField(__('messages.invalid_credentials')));
        }
        if (!$user->status) {
            OtpChannelHelper::send($user, 'register', $user->otp_delivery_method?? "email");
            return [
                "token" => null,
                $this->key => $this->resource::make($user),
                "need_verify" => true,
            ];
        }

        [$token, $tokenId] = $this->issueToken(
            $user,
            $request->input('token_device'),
            [
                'platform'    => $request->input('platform', 'unknown'),
                'device_name' => $request->input('device_name'),
                'device_id'   => $request->input('device_id'),
            ]
        );

        return ['token' => $token,'token_id' => $tokenId, $this->key => $this->resource::make($user),"need_verify" => false];
    }

    public function verifyOtp(BasicRequest $request): array
    {
        /*
         * note: no need for manage this here you can override it in son needed service or even not put the route in the api
         * if ($this->guard !== 'user') {
            throw new HttpResponseException($this->requiredField(__('messages.unavailable_operation')));
        }*/

        if($request->purpose === "phone_change")
            return $this->verifyPhoneChangeOtp($request);

        $col = $request->filled('id')? "id" : "email";
        $user = $this->model::where($col,$request->input($col))->firstOrFail();
        $channel = $user->otp_delivery_method?? "email";

        $ok = OtpChannelHelper::verify($user, $request->input("purpose"), (string) $request->input('otp'), $channel);
        if (!$ok) {
            throw new HttpResponseException($this->requiredField(__('messages.invalid_otp')));
        }

        if (in_array($channel, ['sms','whatsapp'])) {
            $user->phone_status = true;
            $user->phone_verified_at = now();
        } else {
            $user->email_status = true;
            $user->email_verified_at = now();
        }

        $user->status = true;
        $user->activated_at = now();
        $user->save();


        if($request->filled("id") && $user->id === $request->input("id"))
         [$token] = $this->issueToken($user, $request->input('token_device'));

        return [
            'message' => "Your account has been verified successfully.",
            'token' => $token?? null,
            $this->key => $this->resource::make($user->fresh()),
        ];
    }
}

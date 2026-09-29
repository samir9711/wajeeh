<?php

namespace App\Services\Functional;

use App\Http\Requests\Basic\BasicRequest;
use App\Http\Resources\Model\AdminResource;
use App\Models\Admin;
use App\Services\Basic\BaseAuthService;
use App\Support\OtpChannelHelper;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
class AdminAuthService extends BaseAuthService
{
    protected function setVariables(): void
    {
        $this->model    = Admin::class;
        $this->key      = 'admin';
        $this->resource = AdminResource::class;
        $this->guard    = 'admin';
    }

    public function login(BasicRequest $request): array
    {
        $email  = $request->input('email');
        $pass   = $request->input('password');

        $admin = $this->model::where('email', $email)->first();
        if (!$admin || !$admin->password || !Hash::check($pass, $admin->password)) {
            throw new HttpResponseException($this->requiredField(__('messages.invalid_credentials')));
        }
        $admin->loadMissing(['roles', 'permissions']);
        [$token] = $this->issueToken($admin, $request->input('token_device'));
        return ['token' => $token, $this->key => $this->resource::make($admin)];
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

        $user = $this->model::where("email",$request->input('email'))->firstOrFail();
        $channel = $user->otp_delivery_method?? "email";

        $ok = OtpChannelHelper::verify($user, $request->input("purpose"), (string) $request->input('otp'), $channel);
        if (!$ok) {
            throw new HttpResponseException($this->requiredField(__('messages.invalid_otp')));
        }

        return [
            'message' => "",
            $this->key => $this->resource::make($user->fresh()),
        ];
    }
}

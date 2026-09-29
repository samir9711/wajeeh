<?php

namespace App\Http\Controllers;

use App\Http\Requests\Model\Auth\DeviceTokenRequest;
use App\Http\Traits\GeneralTrait;
use Illuminate\Http\Request;

abstract class FatherAuthController
{
    use GeneralTrait;

    protected $key;
    protected $service;

    protected $createRequest;
    protected $updateRequest;

    public function __construct()
    {
        $this->setVariables();
    }

    protected abstract function setVariables(): void;


    public function register(\App\Http\Requests\Model\Auth\RegisterRequest $request)
    {
        try {
            $data = $this->service::register($request);
            return $this->apiResponse($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }


    public function resendRegisterOtp(\App\Http\Requests\Model\Auth\ResendRegisterOtpRequest $request)
    {
        try {
            $data = $this->service::resendRegisterOtp($request);
            return $this->apiResponse($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    // ✅ التحقق من OTP للتسجيل
    public function verifyOtp(\App\Http\Requests\Model\Auth\RegisterVerifyRequest $request)
    {
        try {
            $data = $this->service::verifyOtp($request);
            return $this->apiResponse($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    // ➕ باقي الدوال كما كانت عندك
    public function login(\App\Http\Requests\Model\Auth\LoginRequest $request)
    {
        try {
            $data = $this->service::login($request);
            return $this->apiResponse($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function sendResetOtp(\App\Http\Requests\Model\Auth\ResetPassword\SendResetOtpRequest $request)
    {
        try {
            $this->service::sendResetOtp($request);
            return $this->apiResponse(['message' => __('messages.otp_sent')]);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function verifyResetOtp(\App\Http\Requests\Model\Auth\ResetPassword\VerifyResetOtpRequest $request)
    {
        try {
            $data = $this->service::verifyResetOtp($request);
            return $this->apiResponse($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function resetPassword(\App\Http\Requests\Model\Auth\ResetPassword\ResetPasswordRequest $request)
    {
        try {
            $this->service::resetPassword($request);
            return $this->apiResponse(['message' => __('messages.password_updated')]);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function resendFinalOtp()
    {
        try {
            $this->service::resendFinalOtp(request());
            return $this->apiResponse(['message' => __('messages.otp_resent')]);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function requestPhoneChange(Request $request)
    {
        try {
            $this->service::requestPhoneChange($request);
            return $this->apiResponse(['message' => __('messages.otp_sent')]);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function verifyPhoneChangeOtp(Request $request)
    {
        try {
            $data = $this->service::verifyPhoneChangeOtp($request);
            return $this->apiResponse($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function updateLanguage(Request $request)
    {
        try {
            $data = $this->service::updateLanguage($request);
            return $this->apiResponse($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    public function syncDeviceToken(DeviceTokenRequest $request)
    {
        try {
            $data = $this->service::syncLoggedInDeviceToken($request);
            return $this->apiResponse($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}

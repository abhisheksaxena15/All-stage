<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\CustomerOtpService;
use App\Services\CustomerService;
use Exception;

class CustomerAuthController
{
    private CustomerOtpService $otpService;
    private CustomerService $customerService;

    public function __construct()
    {
        $this->otpService = new CustomerOtpService();
        $this->customerService = new CustomerService();
    }

    /**
     * POST /api/auth/customer/send-otp
     */
    public function sendOtp(): void
    {
        $data = Request::body();
        $email = isset($data['email']) ? trim($data['email']) : '';

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::error(
                "A valid email address is required.",
                422,
                ['email' => 'Valid email address is required.']
            );
            return;
        }

        try {
            $success = $this->otpService->sendOtp($email);
            if ($success) {
                Response::success(
                    ['email' => $email],
                    "OTP sent successfully."
                );
            } else {
                Response::error("Failed to send OTP. Please try again.", 500);
            }
        } catch (Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * POST /api/auth/customer/verify-otp
     */
    public function verifyOtp(): void
    {
        $data = Request::body();
        $email = isset($data['email']) ? trim($data['email']) : '';
        $otp = isset($data['otp']) ? trim($data['otp']) : '';
        $name = isset($data['name']) ? trim($data['name']) : '';
        $phone = isset($data['phone']) ? trim($data['phone']) : null;

        if (empty($email) || empty($otp)) {
            Response::error(
                "Email and OTP are required.",
                422,
                [
                    'email' => empty($email) ? 'Email is required.' : null,
                    'otp' => empty($otp) ? 'OTP is required.' : null
                ]
            );
            return;
        }

        try {
            $isValid = $this->otpService->verifyOtp($email, $otp);

            if (!$isValid) {
                Response::error("Invalid or expired OTP.", 401);
                return;
            }

            // Successfully verified! Now login or register the customer in the database
            $customer = $this->customerService->loginOrRegister($email, $name, $phone);

            Response::success(
                $customer->toArray(),
                "Authentication successful."
            );
        } catch (Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }
}

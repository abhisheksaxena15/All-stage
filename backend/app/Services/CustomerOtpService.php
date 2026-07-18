<?php

namespace App\Services;

use App\Repositories\CustomerOtpRepository;

class CustomerOtpService
{
    private CustomerOtpRepository $repository;
    private MailService $mail;

    public function __construct()
    {
        $this->repository = new CustomerOtpRepository();
        $this->mail = new MailService();
    }

    /**
     * Generate and send a 6-digit OTP to customer email
     */
    public function sendOtp(string $email): bool
    {
        $otp = str_pad(
            random_int(0, 999999),
            6,
            '0',
            STR_PAD_LEFT
        );

        // Delete any old OTPs for this email
        $this->repository->deleteByEmail($email);

        // Insert new OTP
        $this->repository->create(
            $email,
            $otp,
            date('Y-m-d H:i:s', strtotime('+5 minutes'))
        );

        // Send OTP email (using failsafe inside MailService)
        return $this->mail->sendOTP($email, $otp);
    }

    /**
     * Verify customer OTP
     */
    public function verifyOtp(string $email, string $otp): bool
    {
        $validOtp = $this->repository->findValidOtp($email, $otp);

        if (!$validOtp) {
            return false;
        }

        // Mark OTP as used
        $this->repository->markUsed($validOtp->getId());

        return true;
    }
}

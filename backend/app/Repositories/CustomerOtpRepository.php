<?php

namespace App\Repositories;

use App\Models\CustomerOtp;
use PDO;

class CustomerOtpRepository extends BaseRepository
{
    protected string $table = 'customer_otp_codes';
    protected string $model = CustomerOtp::class;

    /**
     * Delete old OTPs of a customer email
     */
    public function deleteByEmail(string $email): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM customer_otp_codes WHERE email = :email"
        );

        return $stmt->execute([
            ':email' => $email
        ]);
    }

    /**
     * Save customer OTP
     */
    public function create(
        string $email,
        string $otp,
        string $expiresAt
    ): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO customer_otp_codes
            (
                email,
                otp,
                expires_at
            )
            VALUES
            (
                :email,
                :otp,
                :expires_at
            )"
        );

        return $stmt->execute([
            ':email' => $email,
            ':otp' => $otp,
            ':expires_at' => $expiresAt
        ]);
    }

    /**
     * Find latest valid OTP
     */
    public function findValidOtp(
        string $email,
        string $otp
    ): ?CustomerOtp {
        $stmt = $this->db->prepare(
            "SELECT *
            FROM customer_otp_codes
            WHERE email = :email
            AND otp = :otp
            AND is_used = 0
            AND expires_at > NOW()
            ORDER BY id DESC
            LIMIT 1"
        );

        $stmt->execute([
            ':email' => $email,
            ':otp' => $otp
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new CustomerOtp($row);
    }

    /**
     * Mark OTP Used
     */
    public function markUsed(int $id): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE customer_otp_codes
             SET is_used = 1
             WHERE id = :id"
        );

        return $stmt->execute([
            ':id' => $id
        ]);
    }
}

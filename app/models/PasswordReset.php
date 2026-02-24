<?php

require_once __DIR__ . '/../../config/db_connection.php';

class PasswordReset
{
    private $connection;

    const OTP_EXPIRY_MINUTES      = 15;
    const RESEND_COOLDOWN_SECONDS = 60;

    public function __construct()
    {
        global $connection;
        $this->connection = $connection;
    }

    // lookup user by username or email, returns user row or null
    public function findUserByIdentifier($identifier)
    {
        $stmt = $this->connection->prepare("
            SELECT id, username, email, first_name, last_name
            FROM users
            WHERE (username = ? OR email = ?)
            AND status = 'active'
            LIMIT 1
        ");
        $stmt->execute([$identifier, $identifier]);
        return $stmt->fetch() ?: null;
    }

    // get existing unused otp record for user
    public function getActiveReset($user_id)
    {
        $stmt = $this->connection->prepare("
            SELECT * FROM password_resets
            WHERE user_id = ?
            AND is_used = 0
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetch() ?: null;
    }

    // check if user is within resend cooldown — let mysql do the time comparison
    public function isWithinCooldown($user_id)
    {
        $stmt = $this->connection->prepare("
            SELECT id FROM password_resets
            WHERE user_id = ?
            AND is_used = 0
            AND last_sent_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $stmt->execute([$user_id, self::RESEND_COOLDOWN_SECONDS]);
        return $stmt->fetch() !== false;
    }

    // seconds remaining before user can resend — let mysql calculate the diff
    public function getCooldownRemaining($user_id)
    {
        $stmt = $this->connection->prepare("
            SELECT GREATEST(0, ? - TIMESTAMPDIFF(SECOND, last_sent_at, NOW())) AS remaining
            FROM password_resets
            WHERE user_id = ?
            AND is_used = 0
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $stmt->execute([self::RESEND_COOLDOWN_SECONDS, $user_id]);
        $result = $stmt->fetch();
        return $result ? (int) $result['remaining'] : 0;
    }

    // invalidate all existing otp records for user then insert a fresh one
    public function createOrRenewOtp($user_id)
    {
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // mark all previous records as used
        $stmt = $this->connection->prepare("
            UPDATE password_resets SET is_used = 1 WHERE user_id = ?
        ");
        $stmt->execute([$user_id]);

        // insert fresh otp using mysql time for consistency
        $stmt = $this->connection->prepare("
            INSERT INTO password_resets (user_id, otp_code, expires_at, last_sent_at)
            VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 15 MINUTE), NOW())
        ");
        $stmt->execute([$user_id, $otp]);

        return $otp;
    }

    // verify otp — checks code, expiry, and used flag
    public function verifyOtp($user_id, $otp_code)
    {
        $stmt = $this->connection->prepare("
            SELECT * FROM password_resets
            WHERE user_id  = ?
            AND otp_code   = ?
            AND is_used    = 0
            AND expires_at > NOW()
            LIMIT 1
        ");
        $stmt->execute([$user_id, $otp_code]);
        return $stmt->fetch() ?: null;
    }

    // mark otp as used after successful verification
    public function markOtpUsed($reset_id)
    {
        $stmt = $this->connection->prepare("
            UPDATE password_resets SET is_used = 1 WHERE id = ?
        ");
        $stmt->execute([$reset_id]);
    }

    // update user password
    public function resetPassword($user_id, $new_password)
    {
        $hashed = password_hash($new_password, PASSWORD_BCRYPT);

        $stmt = $this->connection->prepare("
            UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?
        ");
        return $stmt->execute([$hashed, $user_id]);
    }

    // send otp email via brevo api
    public function sendOtpEmail($to_email, $to_name, $otp_code)
    {
        $api_key = $_ENV['BREVO_API_KEY'] ?? '';

        $html_body = $this->buildOtpEmailHtml($to_name, $otp_code);

        $payload = json_encode([
            'sender'      => [
                'name'  => 'Datamex LMS Support',
                'email' => 'devglensprt@gmail.com',
            ],
            'to'          => [['email' => $to_email, 'name' => $to_name]],
            'subject'     => 'Your Password Reset OTP',
            'htmlContent' => $html_body,
        ]);

        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
                'Content-Type: application/json',
                'api-key: ' . $api_key,
            ],
        ]);

        $response  = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($http_code !== 201) {
            error_log('[PasswordReset::sendOtpEmail] brevo api error: ' . $response);
            return false;
        }

        return true;
    }

    // builds html email body for otp — maroon theme matching school branding
    private function buildOtpEmailHtml($name, $otp)
    {
        $expiry = self::OTP_EXPIRY_MINUTES;
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        </head>
        <body style='margin:0;padding:0;background-color:#f4f6f9;font-family:Arial,sans-serif;'>
            <table width='100%' cellpadding='0' cellspacing='0' style='background-color:#f4f6f9;padding:40px 0;'>
                <tr>
                    <td align='center'>
                        <table width='520' cellpadding='0' cellspacing='0' style='background:#ffffff;border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);'>
                            <tr>
                                <td style='background:linear-gradient(135deg,#85120f 0%,#6d0f0c 100%);padding:30px;text-align:center;'>
                                    <h1 style='color:#ffffff;margin:0;font-size:22px;letter-spacing:1px;'>Password Reset</h1>
                                    <p style='color:rgba(255,255,255,0.8);margin:6px 0 0;font-size:13px;'>Datamex College of Saint Adeline - LMS</p>
                                </td>
                            </tr>
                            <tr>
                                <td style='padding:36px 40px;'>
                                    <p style='color:#333333;font-size:15px;margin:0 0 16px;'>Hello, <strong>{$name}</strong></p>
                                    <p style='color:#555555;font-size:14px;margin:0 0 24px;line-height:1.6;'>
                                        We received a request to reset your password. Use the OTP code below to continue.
                                        This code is valid for <strong>{$expiry} minutes</strong>.
                                    </p>
                                    <div style='text-align:center;margin:0 0 28px;'>
                                        <span style='display:inline-block;background:#fef6f6;border:2px dashed #85120f;border-radius:8px;padding:16px 40px;font-size:36px;font-weight:bold;letter-spacing:10px;color:#85120f;'>
                                            {$otp}
                                        </span>
                                    </div>
                                    <p style='color:#888888;font-size:13px;margin:0;line-height:1.6;'>
                                        If you did not request this, please ignore this email. Your password will remain unchanged.
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td style='background:#f9f9f9;padding:16px 40px;text-align:center;border-top:1px solid #eeeeee;'>
                                    <p style='color:#aaaaaa;font-size:12px;margin:0;'>This is an automated message from Datamex LMS. Please do not reply.</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ";
    }
}

<?php

namespace App;

use PDO;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class LeadManager
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Simpan lead ke database dan kirim email notifikasi
     */
    public function save(array $lead, string $summary, array $history): void
    {
        // Simpan ke DB
        $stmt = $this->pdo->prepare("
            INSERT INTO leads (nama, whatsapp, email, perusahaan, intensi, summary, raw_history)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $lead['nama']       ?? null,
            $lead['whatsapp']   ?? null,
            $lead['email']      ?? null,
            $lead['perusahaan'] ?? null,
            $lead['intensi']    ?? null,
            $summary,
            json_encode($history, JSON_UNESCAPED_UNICODE),
        ]);

        $leadId = $this->pdo->lastInsertId();

        // Kirim email ke semua recipients aktif
        $this->sendEmailNotification($lead, $summary, (int)$leadId);
    }

    private function sendEmailNotification(array $lead, string $summary, int $leadId): void
    {
        $recipients = $this->pdo->query(
            "SELECT name, email FROM email_recipients WHERE active = 1"
        )->fetchAll(PDO::FETCH_ASSOC);

        if (empty($recipients)) return;

        $mail = new PHPMailer(true);

        try {
            // SMTP Config
            $mail->isSMTP();
            $mail->Host       = $_ENV['SMTP_HOST'] ?? '';
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['SMTP_USER'] ?? '';
            $mail->Password   = $_ENV['SMTP_PASS'] ?? '';
            $mail->SMTPSecure = $_ENV['SMTP_SECURE'] === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = (int)($_ENV['SMTP_PORT'] ?? 465);
            $mail->CharSet    = 'UTF-8';

            // From
            $mail->setFrom(
                $_ENV['SMTP_USER'] ?? 'chatbot@kemindogroup.com',
                $_ENV['SMTP_FROM_NAME'] ?? 'Kemindo Chatbot'
            );

            // Recipients
            foreach ($recipients as $r) {
                $mail->addAddress($r['email'], $r['name']);
            }

            // Content
            $mail->isHTML(true);
            $mail->Subject = '🔔 New Lead from Kemindo Chatbot – ' . ($lead['perusahaan'] ?? 'Unknown Company');
            $mail->Body    = $this->buildEmailBody($lead, $summary, $leadId);
            $mail->AltBody = strip_tags($this->buildEmailBodyPlain($lead, $summary));

            $mail->send();
        } catch (\Exception $e) {
            // Log error tapi jangan sampai crash chat
            error_log('LeadManager email error: ' . $e->getMessage());
        }
    }

    private function buildEmailBody(array $lead, string $summary, int $leadId): string
    {
        $nama       = htmlspecialchars($lead['nama']       ?? '-');
        $wa         = htmlspecialchars($lead['whatsapp']   ?? '-');
        $email      = htmlspecialchars($lead['email']      ?? '-');
        $perusahaan = htmlspecialchars($lead['perusahaan'] ?? '-');
        $intensi    = htmlspecialchars($lead['intensi']    ?? '-');
        $summaryHtml = nl2br(htmlspecialchars($summary));
        $time       = date('d M Y, H:i') . ' WIB';

        return <<<HTML
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px;">
<div style="max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    
    <!-- Header -->
    <div style="background: linear-gradient(135deg, #1a3a5c, #2563eb); padding: 24px 32px;">
        <h2 style="color: #fff; margin: 0; font-size: 20px;">🔔 New Potential Customer</h2>
        <p style="color: #93c5fd; margin: 4px 0 0; font-size: 13px;">Via Kemindo Chatbot · {$time}</p>
    </div>

    <!-- Contact Info -->
    <div style="padding: 24px 32px; border-bottom: 1px solid #e5e7eb;">
        <h3 style="color: #1f2937; margin: 0 0 16px; font-size: 15px; text-transform: uppercase; letter-spacing: 0.05em;">Contact Information</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #6b7280; font-size: 13px; width: 130px;">Name</td>
                <td style="padding: 8px 0; color: #111827; font-size: 14px; font-weight: 600;">{$nama}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280; font-size: 13px;">Company</td>
                <td style="padding: 8px 0; color: #111827; font-size: 14px; font-weight: 600;">{$perusahaan}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280; font-size: 13px;">WhatsApp</td>
                <td style="padding: 8px 0; color: #111827; font-size: 14px;">
                    <a href="https://wa.me/{$wa}" style="color: #25d366; text-decoration: none;">+{$wa}</a>
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280; font-size: 13px;">Email</td>
                <td style="padding: 8px 0; color: #111827; font-size: 14px;">
                    <a href="mailto:{$email}" style="color: #2563eb; text-decoration: none;">{$email}</a>
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280; font-size: 13px;">Intention</td>
                <td style="padding: 8px 0; color: #111827; font-size: 14px;">{$intensi}</td>
            </tr>
        </table>
    </div>

    <!-- Summary -->
    <div style="padding: 24px 32px; border-bottom: 1px solid #e5e7eb;">
        <h3 style="color: #1f2937; margin: 0 0 12px; font-size: 15px; text-transform: uppercase; letter-spacing: 0.05em;">Conversation Summary</h3>
        <div style="background: #f9fafb; border-left: 3px solid #2563eb; padding: 16px; border-radius: 4px; color: #374151; font-size: 14px; line-height: 1.6;">
            {$summaryHtml}
        </div>
    </div>

    <!-- Footer -->
    <div style="padding: 20px 32px; background: #f9fafb;">
        <p style="margin: 0; color: #9ca3af; font-size: 12px; text-align: center;">
            This email was automatically generated by Kemindo Chatbot.<br>
            Lead ID: #{$leadId}
        </p>
    </div>
</div>
</body>
</html>
HTML;
    }

    private function buildEmailBodyPlain(array $lead, string $summary): string
    {
        return "New Potential Customer via Kemindo Chatbot\n\n"
            . "Name: "      . ($lead['nama']       ?? '-') . "\n"
            . "Company: "   . ($lead['perusahaan'] ?? '-') . "\n"
            . "WhatsApp: "  . ($lead['whatsapp']   ?? '-') . "\n"
            . "Email: "     . ($lead['email']       ?? '-') . "\n"
            . "Intention: " . ($lead['intensi']    ?? '-') . "\n\n"
            . "Summary:\n"  . $summary . "\n";
    }
}

<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/env.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as MailException;

/**
 * Wrapper ligero sobre PHPMailer.
 * Requiere: composer require phpmailer/phpmailer
 * Configuración: variables MAIL_* en backend/.env
 */
final class Mailer
{
    /**
     * Envía un email HTML.
     *
     * @param string $toEmail   Destinatario
     * @param string $toName    Nombre del destinatario
     * @param string $subject   Asunto
     * @param string $bodyHtml  Cuerpo HTML
     * @param string $bodyText  Cuerpo texto plano (fallback)
     *
     * @throws RuntimeException si falla el envío
     */
    public static function send(
        string $toEmail,
        string $toName,
        string $subject,
        string $bodyHtml,
        string $bodyText = ''
    ): void {
        $autoloadPath = __DIR__ . '/../../vendor/autoload.php';

        if (!file_exists($autoloadPath)) {
            throw new RuntimeException(
                'PHPMailer no está instalado. Ejecuta: cd backend && composer install'
            );
        }

        require_once $autoloadPath;

        $mail = new PHPMailer(true);

        try {
            // Servidor SMTP
            $mail->isSMTP();
            $mail->Host       = getenv('MAIL_HOST')       ?: 'smtp.gmail.com';
            $mail->Port       = (int) (getenv('MAIL_PORT') ?: 587);
            $mail->SMTPAuth   = true;
            $mail->Username   = getenv('MAIL_USERNAME')   ?: '';
            $mail->Password   = getenv('MAIL_PASSWORD')   ?: '';

            $encryption = strtolower(getenv('MAIL_ENCRYPTION') ?: 'tls');
            $mail->SMTPSecure = ($encryption === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;

            // Remitente
            $fromAddress = getenv('MAIL_FROM_ADDRESS') ?: $mail->Username;
            $fromName    = getenv('MAIL_FROM_NAME')    ?: 'Mega Uni Store';
            $mail->setFrom($fromAddress, $fromName);

            // Destinatario
            $mail->addAddress($toEmail, $toName);

            // Contenido
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $subject;
            $mail->Body    = $bodyHtml;
            $mail->AltBody = $bodyText !== '' ? $bodyText : strip_tags($bodyHtml);

            $mail->send();
        } catch (MailException $e) {
            throw new RuntimeException('Error al enviar el correo: ' . $mail->ErrorInfo);
        }
    }

    /**
     * Construye el HTML base para emails del sistema.
     */
    public static function plantillaBase(string $titulo, string $contenido): string
    {
        return <<<HTML
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width,initial-scale=1">
            <title>{$titulo}</title>
        </head>
        <body style="margin:0;padding:0;background:#f3f6fb;font-family:Arial,Helvetica,sans-serif;">
            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f3f6fb;padding:40px 20px;">
                <tr>
                    <td align="center">
                        <table width="100%" style="max-width:560px;background:#ffffff;border-radius:18px;box-shadow:0 12px 36px rgba(15,23,42,.10);border:1px solid #dbe3ef;overflow:hidden;">
                            <tr>
                                <td style="background:linear-gradient(135deg,#1e3a8a,#2563eb);padding:28px 32px;">
                                    <h1 style="margin:0;color:#ffffff;font-size:22px;font-weight:800;">Mega Uni Store</h1>
                                    <p style="margin:6px 0 0;color:rgba(255,255,255,.8);font-size:13px;">Sistema de gestión multitienda</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:32px;">
                                    <h2 style="margin:0 0 18px;color:#172554;font-size:20px;">{$titulo}</h2>
                                    {$contenido}
                                </td>
                            </tr>
                            <tr>
                                <td style="background:#f8fafc;padding:18px 32px;border-top:1px solid #e5e7eb;">
                                    <p style="margin:0;color:#9ca3af;font-size:12px;">
                                        Este es un mensaje automático del sistema Mega Uni Store.
                                        No respondas a este correo.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        HTML;
    }
}

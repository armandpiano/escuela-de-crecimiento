<?php
/**
 * =============================================================================
 * SERVICIO DE EMAIL - INFRAESTRUCTURA
 * Christian LMS System - Infrastructure Layer
 * =============================================================================
 */

namespace ChristianLMS\Infrastructure\Mail;

use ChristianLMS\Domain\Entities\User;
use ChristianLMS\Infrastructure\Mail\Exceptions\EmailException;

/**
 * Servicio de Email
 * 
 * Maneja el envío de correos electrónicos del sistema
 * usando configuración de la aplicación.
 */
class EmailService
{
    /** @var array */
    private $config;
    /** @var \PHPMailer\PHPMailer\PHPMailer */
    private $mailer;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->initializeMailer();
    }

    /**
     * Inicializar PHPMailer
     */
    private function initializeMailer(): void
    {
        $this->mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
        
        // Configuración del servidor SMTP
        $this->mailer->isSMTP();
        $this->mailer->Host = $this->config['host'] ?? 'smtp.gmail.com';
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $this->config['username'] ?? '';
        $this->mailer->Password = $this->config['password'] ?? '';
        $this->mailer->SMTPSecure = $this->config['encryption'] ?? 'tls';
        $this->mailer->Port = $this->config['port'] ?? 587;
        $this->mailer->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        // Configuración por defecto
        $this->mailer->setFrom(
            $this->config['from_address'] ?? 'noreply@churchlms.com',
            $this->config['from_name'] ?? 'Christian LMS System'
        );
        $this->mailer->isHTML(true);
        $this->mailer->CharSet = 'UTF-8';
    }

    /**
     * Enviar email de bienvenida
     */
    public function sendWelcomeEmail(User $user): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($user->getEmailString(), $user->getFullName());
            
            $this->mailer->Subject = '¡Bienvenido a Christian LMS System!';
            
            $body = $this->buildWelcomeEmailTemplate($user);
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);

            return $this->send();
            
        } catch (\Exception $e) {
            throw new EmailException("Error enviando email de bienvenida: " . $e->getMessage());
        }
    }

    /**
     * Enviar email de verificación
     */
    public function sendVerificationEmail(User $user, string $verificationUrl = null): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($user->getEmailString(), $user->getFullName());
            
            $this->mailer->Subject = 'Verifica tu cuenta - Christian LMS System';
            
            $verificationToken = $this->generateVerificationToken($user);
            if (!$verificationUrl) {
                $verificationUrl = $this->buildVerificationUrl($verificationToken);
            }
            
            $body = $this->buildVerificationEmailTemplate($user, $verificationUrl);
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);

            return $this->send();
            
        } catch (\Exception $e) {
            throw new EmailException("Error enviando email de verificación: " . $e->getMessage());
        }
    }

    /**
     * Enviar email de reset de contraseña
     */
    public function sendPasswordResetEmail(User $user, string $resetUrl = null): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($user->getEmailString(), $user->getFullName());
            
            $this->mailer->Subject = 'Restablecer contraseña - Christian LMS System';
            
            $resetToken = $this->generateResetToken($user);
            if (!$resetUrl) {
                $resetUrl = $this->buildResetPasswordUrl($resetToken);
            }
            
            $body = $this->buildPasswordResetEmailTemplate($user, $resetUrl);
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);

            return $this->send();
            
        } catch (\Exception $e) {
            throw new EmailException("Error enviando email de reset de contraseña: " . $e->getMessage());
        }
    }

    /**
     * Enviar notificación de cambio de contraseña
     */
    public function sendPasswordChangedNotification(User $user): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($user->getEmailString(), $user->getFullName());
            
            $this->mailer->Subject = 'Contraseña actualizada - Christian LMS System';
            
            $body = $this->buildPasswordChangedTemplate($user);
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);

            return $this->send();
            
        } catch (\Exception $e) {
            throw new EmailException("Error enviando notificación de cambio de contraseña: " . $e->getMessage());
        }
    }

    /**
     * Enviar email de notificación de actividad sospechosa
     */
    public function sendSuspiciousActivityEmail(User $user, string $activity): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($user->getEmailString(), $user->getFullName());
            
            $this->mailer->Subject = 'Actividad inusual detectada - Christian LMS System';
            
            $body = $this->buildSuspiciousActivityTemplate($user, $activity);
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);

            return $this->send();
            
        } catch (\Exception $e) {
            throw new EmailException("Error enviando notificación de actividad sospechosa: " . $e->getMessage());
        }
    }

    /**
     * Enviar email personalizado
     */
    public function sendCustomEmail(User $user, string $subject, string $body): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($user->getEmailString(), $user->getFullName());
            
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);

            return $this->send();
            
        } catch (\Exception $e) {
            throw new EmailException("Error enviando email personalizado: " . $e->getMessage());
        }
    }

    /**
     * Enviar email a múltiples usuarios
     */
    public function sendBulkEmail(array $users, string $subject, string $body): bool
    {
        try {
            $this->mailer->clearAddresses();
            
            foreach ($users as $user) {
                $this->mailer->addAddress($user->getEmailString(), $user->getFullName());
            }
            
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);

            return $this->send();
            
        } catch (\Exception $e) {
            throw new EmailException("Error enviando email masivo: " . $e->getMessage());
        }
    }

    /**
     * Enviar email
     */
    private function send(): bool
    {
        try {
            $result = $this->mailer->send();
            
            if (!$result) {
                throw new EmailException("Error enviando email: " . $this->mailer->ErrorInfo);
            }
            
            return true;
            
        } catch (\Exception $e) {
            error_log("Error enviando email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Construir template de email de bienvenida
     */
    private function buildWelcomeEmailTemplate(User $user): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Bienvenido</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
                .btn { display: inline-block; padding: 12px 24px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>¡Bienvenido a Christian LMS!</h1>
                </div>
                <div class='content'>
                    <p>Hola {$user->getFirstName()},</p>
                    <p>¡Nos complace darte la bienvenida a nuestro sistema de gestión escolar! Tu cuenta ha sido creada exitosamente.</p>
                    <p>Con Christian LMS podrás:</p>
                    <ul>
                        <li>Acceder a tus cursos y materiales</li>
                        <li>Participar en clases virtuales</li>
                        <li>Enviar tareas y actividades</li>
                        <li>Consultar tus calificaciones</li>
                        <li>Comunicarte con profesores y compañeros</li>
                    </ul>
                    <p>Si tienes alguna pregunta, no dudes en contactarnos.</p>
                    <p style='text-align: center; margin-top: 30px;'>
                        <a href='" . env('APP_URL') . "' class='btn'>Ir a mi cuenta</a>
                    </p>
                </div>
                <div class='footer'>
                    <p>Christian LMS System - Sistema de Gestión Educativa</p>
                    <p>© 2024 Todos los derechos reservados</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Construir template de email de verificación
     */
    private function buildVerificationEmailTemplate(User $user, string $verificationUrl): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Verifica tu cuenta</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #007bff; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
                .btn { display: inline-block; padding: 12px 24px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; }
                .code { background: #f8f9fa; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; border-radius: 5px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Verifica tu cuenta</h1>
                </div>
                <div class='content'>
                    <p>Hola {$user->getFirstName()},</p>
                    <p>Para completar tu registro, necesitas verificar tu dirección de correo electrónico.</p>
                    <p>Haz clic en el botón de abajo para verificar tu cuenta:</p>
                    <p style='text-align: center; margin: 30px 0;'>
                        <a href='{$verificationUrl}' class='btn'>Verificar cuenta</a>
                    </p>
                    <p>Si el botón no funciona, puedes copiar y pegar este enlace en tu navegador:</p>
                    <div class='code'>{$verificationUrl}</div>
                    <p><strong>Nota:</strong> Este enlace expirará en 24 horas por seguridad.</p>
                    <p>Si no creaste esta cuenta, puedes ignorar este email.</p>
                </div>
                <div class='footer'>
                    <p>Christian LMS System</p>
                    <p>© 2024 Todos los derechos reservados</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Construir template de reset de contraseña
     */
    private function buildPasswordResetEmailTemplate(User $user, string $resetUrl): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Restablecer contraseña</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #dc3545; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
                .btn { display: inline-block; padding: 12px 24px; background: #dc3545; color: white; text-decoration: none; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Restablecer contraseña</h1>
                </div>
                <div class='content'>
                    <p>Hola {$user->getFirstName()},</p>
                    <p>Recibimos una solicitud para restablecer la contraseña de tu cuenta.</p>
                    <p>Haz clic en el botón de abajo para crear una nueva contraseña:</p>
                    <p style='text-align: center; margin: 30px 0;'>
                        <a href='{$resetUrl}' class='btn'>Restablecer contraseña</a>
                    </p>
                    <p><strong>Importante:</strong> Este enlace expirará en 1 hora por seguridad.</p>
                    <p>Si no solicitaste restablecer tu contraseña, puedes ignorar este email.</p>
                </div>
                <div class='footer'>
                    <p>Christian LMS System</p>
                    <p>© 2024 Todos los derechos reservados</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Construir template de cambio de contraseña
     */
    private function buildPasswordChangedTemplate(User $user): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Contraseña actualizada</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #28a745; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Contraseña actualizada</h1>
                </div>
                <div class='content'>
                    <p>Hola {$user->getFirstName()},</p>
                    <p>Te informamos que la contraseña de tu cuenta ha sido actualizada exitosamente.</p>
                    <p>Si no realizaste este cambio, contacta inmediatamente a nuestro equipo de soporte.</p>
                    <p>Fecha del cambio: " . date('d/m/Y H:i:s') . "</p>
                </div>
                <div class='footer'>
                    <p>Christian LMS System</p>
                    <p>© 2024 Todos los derechos reservados</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Construir template de actividad sospechosa
     */
    private function buildSuspiciousActivityTemplate(User $user, string $activity): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Actividad inusual detectada</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #ffc107; color: #212529; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
                .warning { background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; border-radius: 5px; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Actividad inusual detectada</h1>
                </div>
                <div class='content'>
                    <p>Hola {$user->getFirstName()},</p>
                    <p>Hemos detectado actividad inusual en tu cuenta:</p>
                    <div class='warning'>
                        <strong>Actividad:</strong> {$activity}<br>
                        <strong>Fecha:</strong> " . date('d/m/Y H:i:s') . "<br>
                        <strong>IP:</strong> " . ($_SERVER['REMOTE_ADDR'] ?? 'Desconocida') . "
                    </div>
                    <p>Si reconoces esta actividad, puedes ignorar este mensaje.</p>
                    <p>Si no fuiste tú, te recomendamos:</p>
                    <ol>
                        <li>Cambiar tu contraseña inmediatamente</li>
                        <li>Revisar tu cuenta para actividad no autorizada</li>
                        <li>Contactar a nuestro equipo de soporte</li>
                    </ol>
                </div>
                <div class='footer'>
                    <p>Christian LMS System - Seguridad</p>
                    <p>© 2024 Todos los derechos reservados</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Generar token de verificación
     */
    private function generateVerificationToken(User $user): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Generar token de reset
     */
    private function generateResetToken(User $user): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Construir URL de verificación
     */
    private function buildVerificationUrl(string $token): string
    {
        return env('APP_URL') . '/verify-email?token=' . $token;
    }

    /**
     * Construir URL de reset de contraseña
     */
    private function buildResetPasswordUrl(string $token): string
    {
        return env('APP_URL') . '/reset-password?token=' . $token;
    }
}

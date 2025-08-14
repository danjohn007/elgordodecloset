<?php

namespace App\Services;

class MailerService
{
    private $config;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../../config/app.php';
    }

    public function sendReservationConfirmation($reservation, $restaurant)
    {
        $subject = "Confirmación de Reserva - {$restaurant['name']}";
        
        $body = $this->buildReservationConfirmationTemplate($reservation, $restaurant);
        
        return $this->sendEmail(
            $reservation['customer_email'],
            $reservation['customer_name'],
            $subject,
            $body
        );
    }

    public function sendReservationCancellation($reservation, $restaurant, $reason = null)
    {
        $subject = "Cancelación de Reserva - {$restaurant['name']}";
        
        $body = $this->buildReservationCancellationTemplate($reservation, $restaurant, $reason);
        
        return $this->sendEmail(
            $reservation['customer_email'],
            $reservation['customer_name'],
            $subject,
            $body
        );
    }

    public function sendReservationReminder($reservation, $restaurant)
    {
        $subject = "Recordatorio de Reserva - {$restaurant['name']}";
        
        $body = $this->buildReservationReminderTemplate($reservation, $restaurant);
        
        return $this->sendEmail(
            $reservation['customer_email'],
            $reservation['customer_name'],
            $subject,
            $body
        );
    }

    public function sendRestaurantNotification($restaurantEmail, $restaurantName, $subject, $message)
    {
        return $this->sendEmail($restaurantEmail, $restaurantName, $subject, $message);
    }

    public function sendPasswordReset($userEmail, $userName, $resetToken)
    {
        $subject = "Restablecimiento de Contraseña";
        $resetUrl = $this->config['url'] . "/reset-password?token=" . $resetToken;
        
        $body = $this->buildPasswordResetTemplate($userName, $resetUrl);
        
        return $this->sendEmail($userEmail, $userName, $subject, $body);
    }

    public function sendWelcomeEmail($userEmail, $userName, $userRole)
    {
        $subject = "Bienvenido a nuestro Sistema de Reservas";
        
        $body = $this->buildWelcomeTemplate($userName, $userRole);
        
        return $this->sendEmail($userEmail, $userName, $subject, $body);
    }

    private function sendEmail($toEmail, $toName, $subject, $body)
    {
        $headers = [
            'From' => $this->config['mail']['from_name'] . ' <' . $this->config['mail']['from_email'] . '>',
            'Reply-To' => $this->config['mail']['from_email'],
            'Content-Type' => 'text/html; charset=UTF-8',
            'MIME-Version' => '1.0'
        ];

        $headerString = '';
        foreach ($headers as $key => $value) {
            $headerString .= $key . ': ' . $value . "\r\n";
        }

        // Simple mail function - in production, use PHPMailer or similar
        $success = mail($toEmail, $subject, $body, $headerString);
        
        // Log email attempt
        $this->logEmail($toEmail, $subject, $success);
        
        return $success;
    }

    private function buildReservationConfirmationTemplate($reservation, $restaurant)
    {
        $date = date('d/m/Y', strtotime($reservation['reservation_date']));
        $time = date('H:i', strtotime($reservation['reservation_time']));
        
        return "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #007bff; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f8f9fa; }
                .reservation-details { background-color: white; padding: 15px; margin: 15px 0; border-left: 4px solid #007bff; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Reserva Confirmada</h1>
                </div>
                <div class='content'>
                    <p>Estimado/a {$reservation['customer_name']},</p>
                    
                    <p>Su reserva ha sido confirmada exitosamente. A continuación los detalles:</p>
                    
                    <div class='reservation-details'>
                        <h3>Detalles de la Reserva</h3>
                        <p><strong>Restaurante:</strong> {$restaurant['name']}</p>
                        <p><strong>Código de Confirmación:</strong> {$reservation['confirmation_code']}</p>
                        <p><strong>Fecha:</strong> {$date}</p>
                        <p><strong>Hora:</strong> {$time}</p>
                        <p><strong>Número de Personas:</strong> {$reservation['party_size']}</p>
                        <p><strong>Dirección:</strong> {$restaurant['address']}</p>
                        <p><strong>Teléfono:</strong> {$restaurant['phone']}</p>
                    </div>
                    
                    " . ($reservation['special_requests'] ? "<p><strong>Solicitudes Especiales:</strong> {$reservation['special_requests']}</p>" : "") . "
                    
                    <p>Por favor, llegue puntualmente y presente su código de confirmación.</p>
                    
                    <p>¡Esperamos que disfrute su experiencia gastronómica!</p>
                </div>
                <div class='footer'>
                    <p>Este es un correo automático, por favor no responda.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    private function buildReservationCancellationTemplate($reservation, $restaurant, $reason)
    {
        $date = date('d/m/Y', strtotime($reservation['reservation_date']));
        $time = date('H:i', strtotime($reservation['reservation_time']));
        
        return "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #dc3545; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f8f9fa; }
                .reservation-details { background-color: white; padding: 15px; margin: 15px 0; border-left: 4px solid #dc3545; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Reserva Cancelada</h1>
                </div>
                <div class='content'>
                    <p>Estimado/a {$reservation['customer_name']},</p>
                    
                    <p>Lamentamos informarle que su reserva ha sido cancelada.</p>
                    
                    <div class='reservation-details'>
                        <h3>Detalles de la Reserva Cancelada</h3>
                        <p><strong>Restaurante:</strong> {$restaurant['name']}</p>
                        <p><strong>Código de Confirmación:</strong> {$reservation['confirmation_code']}</p>
                        <p><strong>Fecha:</strong> {$date}</p>
                        <p><strong>Hora:</strong> {$time}</p>
                        <p><strong>Número de Personas:</strong> {$reservation['party_size']}</p>
                    </div>
                    
                    " . ($reason ? "<p><strong>Motivo de Cancelación:</strong> {$reason}</p>" : "") . "
                    
                    <p>Si desea hacer una nueva reserva, puede hacerlo a través de nuestro sitio web.</p>
                    
                    <p>Esperamos poder servirle en otra ocasión.</p>
                </div>
                <div class='footer'>
                    <p>Este es un correo automático, por favor no responda.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    private function buildReservationReminderTemplate($reservation, $restaurant)
    {
        $date = date('d/m/Y', strtotime($reservation['reservation_date']));
        $time = date('H:i', strtotime($reservation['reservation_time']));
        
        return "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #28a745; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f8f9fa; }
                .reservation-details { background-color: white; padding: 15px; margin: 15px 0; border-left: 4px solid #28a745; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Recordatorio de Reserva</h1>
                </div>
                <div class='content'>
                    <p>Estimado/a {$reservation['customer_name']},</p>
                    
                    <p>Le recordamos que tiene una reserva confirmada para mañana.</p>
                    
                    <div class='reservation-details'>
                        <h3>Detalles de su Reserva</h3>
                        <p><strong>Restaurante:</strong> {$restaurant['name']}</p>
                        <p><strong>Código de Confirmación:</strong> {$reservation['confirmation_code']}</p>
                        <p><strong>Fecha:</strong> {$date}</p>
                        <p><strong>Hora:</strong> {$time}</p>
                        <p><strong>Número de Personas:</strong> {$reservation['party_size']}</p>
                        <p><strong>Dirección:</strong> {$restaurant['address']}</p>
                        <p><strong>Teléfono:</strong> {$restaurant['phone']}</p>
                    </div>
                    
                    <p>Por favor, llegue puntualmente y presente su código de confirmación.</p>
                    
                    <p>Si necesita cancelar o modificar su reserva, contáctenos con anticipación.</p>
                    
                    <p>¡Esperamos verle pronto!</p>
                </div>
                <div class='footer'>
                    <p>Este es un correo automático, por favor no responda.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    private function buildPasswordResetTemplate($userName, $resetUrl)
    {
        return "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #007bff; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f8f9fa; }
                .button { display: inline-block; padding: 12px 24px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px; margin: 10px 0; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Restablecimiento de Contraseña</h1>
                </div>
                <div class='content'>
                    <p>Hola {$userName},</p>
                    
                    <p>Recibimos una solicitud para restablecer la contraseña de su cuenta.</p>
                    
                    <p>Para crear una nueva contraseña, haga clic en el siguiente enlace:</p>
                    
                    <p><a href='{$resetUrl}' class='button'>Restablecer Contraseña</a></p>
                    
                    <p>Si no solicitó este cambio, puede ignorar este correo.</p>
                    
                    <p>Este enlace expirará en 24 horas por motivos de seguridad.</p>
                </div>
                <div class='footer'>
                    <p>Este es un correo automático, por favor no responda.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    private function buildWelcomeTemplate($userName, $userRole)
    {
        $roleText = [
            'cliente' => 'cliente',
            'admin_restaurante' => 'administrador de restaurante',
            'superadmin' => 'super administrador'
        ][$userRole] ?? 'usuario';

        return "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #28a745; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f8f9fa; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>¡Bienvenido!</h1>
                </div>
                <div class='content'>
                    <p>Estimado/a {$userName},</p>
                    
                    <p>¡Bienvenido a nuestro Sistema de Reservas de Restaurantes!</p>
                    
                    <p>Su cuenta como {$roleText} ha sido creada exitosamente.</p>
                    
                    <p>Ahora puede acceder a todas las funcionalidades de nuestra plataforma.</p>
                    
                    <p>¡Esperamos que tenga una excelente experiencia!</p>
                </div>
                <div class='footer'>
                    <p>Este es un correo automático, por favor no responda.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    private function logEmail($to, $subject, $success)
    {
        $logFile = __DIR__ . '/../../storage/logs/mail.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logEntry = date('Y-m-d H:i:s') . " - TO: {$to} - SUBJECT: {$subject} - SUCCESS: " . ($success ? 'YES' : 'NO') . PHP_EOL;
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}
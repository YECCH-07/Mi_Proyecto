<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

class NotificationService {

    private $mailer;

    public function __construct() {
        // Load environment variables from .env file
        $this->loadEnv(__DIR__ . '/../.env');

        $this->mailer = new PHPMailer(true);

        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host       = getenv('SMTP_HOST');
            $this->mailer->SMTPAuth   = true;
            $this->mailer->Username   = getenv('SMTP_USER');
            $this->mailer->Password   = getenv('SMTP_PASS');
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port       = getenv('SMTP_PORT');
            
            //Recipients
            $from_email = getenv('SMTP_FROM_EMAIL');
            $from_name = getenv('SMTP_FROM_NAME');
            if ($from_email && $from_name) {
                $this->mailer->setFrom($from_email, $from_name);
            }

        } catch (Exception $e) {
            // Handle exceptions during initialization, maybe log them
            // "Mailer Error: {$this->mailer->ErrorInfo}"
        }
    }

    public function sendDenunciaStatusUpdate($to_email, $to_name, $denuncia_codigo, $denuncia_titulo, $nuevo_estado) {
        try {
            $this->mailer->addAddress($to_email, $to_name);

            // Content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = "Actualización de tu denuncia: " . $denuncia_codigo;
            $this->mailer->Body    = "Hola " . $to_name . ",<br><br>Tu denuncia \"<b>" . $denuncia_titulo . "</b>\" (código: " . $denuncia_codigo . ") ha sido actualizada al estado: <b>" . $nuevo_estado . "</b>.<br><br>Puedes consultar el estado en cualquier momento en nuestro portal.<br><br>Gracias,<br>El equipo de Denuncia Ciudadana.";
            $this->mailer->AltBody = "Hola " . $to_name . ",\n\nTu denuncia \"" . $denuncia_titulo . "\" (código: " . $denuncia_codigo . ") ha sido actualizada al estado: " . $nuevo_estado . ".\n\nPuedes consultar el estado en cualquier momento en nuestro portal.\n\nGracias,\nEl equipo de Denuncia Ciudadana.";

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            // "Message could not be sent. Mailer Error: {$this->mailer->ErrorInfo}"
            return false;
        }
    }

    private function loadEnv($path) {
        if (!file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            if (!getenv($name)) {
                putenv(sprintf('%s=%s', $name, $value));
            }
        }
    }
}

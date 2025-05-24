<?php

namespace Core;

use Core\LocalMailer;

class Logger {
    private static string $logFile = BASE_PATH . '/storage/logs/error.log';
    private static string $adminEmail;

    public static function init(): void {
        self::$adminEmail = $_ENV['ERROR_EMAIL'];
    }    

    public static function error(string $message, bool $sendEmail = false): void {
        self::log('ERROR', $message);
        if ($sendEmail) {
            self::sendEmail('Critical Error Reported', $message);
        }
    }

    public static function log(string $level, string $message): void {
        $date = date('Y-m-d H:i:s');
        $logMessage = "[$date] [$level] $message" . PHP_EOL;
        file_put_contents(self::$logFile, $logMessage, FILE_APPEND);
    }

    private static function sendEmail(string $subject, string $message): void {
        $headers = "From: no-reply@example.com\r\n" .
                   "Reply-To: no-reply@example.com\r\n" .
                   "X-Mailer: PHP/" . phpversion();
        mail(self::$adminEmail, $subject, $message, $headers);
        
    }
}
<?php

namespace Core;

use Core\LocalMailer;
use Core\Http\Request;
use Core\Http\Response;

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

    public static function accessLog(Request $request, Response $response): void {
        $timestamp = date('[D M d H:i:s Y]');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
        $port = $_SERVER['REMOTE_PORT'] ?? '';
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];
        $status = $response->getStatusCode();

        $logLine = sprintf(
            "%s [%s]:%s [%d]: %s %s\n",
            $timestamp,
            $ip,
            $port,
            $status,
            $method,
            $uri
        );

        file_put_contents(BASE_PATH . '/storage/logs/access.log', $logLine, FILE_APPEND);
    }
}
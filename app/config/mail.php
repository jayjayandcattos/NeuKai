<?php
/**
 * Mail configuration, read from .env.
 */

return [
    'host' => (string) env('MAIL_HOST', 'smtp.gmail.com'),
    'port' => (int) env('MAIL_PORT', 465),
    'encryption' => (string) env('MAIL_ENCRYPTION', 'smtps'),
    'username' => (string) env('MAIL_USERNAME', ''),
    'password' => (string) env('MAIL_PASSWORD', ''),
    'from_address' => (string) env('MAIL_FROM', env('MAIL_USERNAME', 'no-reply@localhost')),
    'from_name' => (string) env('MAIL_FROM_NAME', 'Neukai Team'),
];

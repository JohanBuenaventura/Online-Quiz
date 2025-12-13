<?php
// Local configuration for mail / app. DO NOT commit real credentials to VCS.
// You can either set environment variables or fill these values.

return [
    'smtp_host' => getenv('SMTP_HOST') ?: 'smtp.gmail.com',
    'smtp_user' => getenv('SMTP_USER') ?: 'oquizsystem@gmail.com',
    'smtp_pass' => getenv('SMTP_PASS') ?: 'ideu ylpu ejzu iikf',
    'smtp_port' => getenv('SMTP_PORT') ?: 465,
    'smtp_secure' => getenv('SMTP_SECURE') ?: 'ssl',
    'smtp_from' => getenv('SMTP_FROM') ?: 'oquizsystem@gmail.com',
    'smtp_from_name' => getenv('SMTP_FROM_NAME') ?: 'Online Quiz System',

    // Optional: OpenAI API key (leave empty unless needed)
    'gemini_api_key' => getenv('AIzaSyDZunZNFI1mnaT5tAOInHYNWNmW6pQ0bXo') ?: '',

    // Max upload size (default 10 MB)
    'max_upload_size' => getenv('MAX_UPLOAD_SIZE') ?: 10485760,
];

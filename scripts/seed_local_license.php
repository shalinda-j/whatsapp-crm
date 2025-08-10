<?php
// Seed a sample local license into the database for testing.
// Usage: run from CLI with `php scripts/seed_local_license.php`

declare(strict_types=1);

require_once __DIR__ . '/../include/conn.php';
require_once __DIR__ . '/../include/function.php';

$customer = $argv[1] ?? 'Test User';
$phone    = preg_replace('/\D/', '', ($argv[2] ?? '5599999999999'));
$email    = $argv[3] ?? 'test@example.com';
$days     = isset($argv[4]) ? (int)$argv[4] : 14;
$userId   = 1; // default admin user id

if ($phone === '') {
    fwrite(STDERR, "Phone is required.\n");
    exit(1);
}

if (check_number($phone)) {
    fwrite(STDOUT, "Phone already exists, skipping insert.\n");
    exit(0);
}

$licenseKey = generate_license(16);
$today = date('Y-m-d H:i:s');
$end   = date('Y-m-d H:i:s', strtotime("+{$days} days"));

$stmt = $conn->prepare("INSERT INTO `users`
    (`user_id`, `customer_name`, `whatsapp_number`, `email`, `license_key`, `act_date`, `end_date`, `life_time`, `plan_type`, `status`, `plan`)
    VALUES (?, ?, ?, ?, ?, ?, ?, 'false', 'Premium', 'true', 'true')");
// Bind 1 int + 6 strings
$stmt->bind_param('issssss', $userId, $customer, $phone, $email, $licenseKey, $today, $end);

if ($stmt->execute()) {
    fwrite(STDOUT, json_encode(['ok' => true, 'license' => $licenseKey, 'end_date' => $end], JSON_PRETTY_PRINT) . "\n");
} else {
    fwrite(STDERR, "Insert failed: " . $stmt->error . "\n");
    exit(1);
}


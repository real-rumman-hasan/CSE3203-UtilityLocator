<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$user = current_user();
clear_remember_token($user['id'] ?? null);
logout_user();
set_flash('success', 'You have been logged out successfully.');
redirect('index.php');

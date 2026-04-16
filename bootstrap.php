<?php
declare(strict_types=1);

require_once __DIR__ . '/db_connect.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

const APP_NAME = 'Utility Service Provider System';
const BRAND_COLOR = '#1F5EFF';
const SUPPORT_EMAIL = 'support@utilitylocator.local';
const GOOGLE_MAPS_EMBED = 'https://maps.google.com/maps?q=23.8103,90.4125&z=12&output=embed';
const GOOGLE_MAPS_API_KEY = 'YOUR_GOOGLE_MAPS_API_KEY';
const APP_URL = 'http://localhost/CSE3203-UtilityLocator-main';
const SSL_SANDBOX_MODE = true;
const SSLCOMMERZ_STORE_ID = 'yeasi69d91d8190674';
const SSLCOMMERZ_STORE_PASSWORD = 'yeasi69d91d8190674@ssl';

function pdo(): PDO
{
    return get_pdo();
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit();
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function get_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function current_user(): ?array
{
    return $_SESSION['auth_user'] ?? null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function require_login(): array
{
    $user = current_user();
    if ($user === null) {
        set_flash('error', 'Please login to continue.');
        redirect('login.php');
    }

    return $user;
}

function require_role(string $role): array
{
    $user = require_login();
    if (($user['role'] ?? null) !== $role) {
        set_flash('error', 'You are not allowed to access that page.');
        redirect('index.php');
    }

    return $user;
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['auth_user'] = [
        'id' => (int) $user['id'],
        'name' => trim($user['f_name'] . ' ' . $user['l_name']),
        'email' => $user['email'],
        'role' => $user['role'],
        'image' => $user['image'],
        'is_verified' => (int) $user['is_verified'],
    ];
}

function logout_user(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', $params['secure'] ?? false, $params['httponly'] ?? true);
    }

    session_destroy();
}

function login_redirect_path(string $role): string
{
    return match ($role) {
        'admin' => 'admin_dashboard.php',
        'provider' => 'provider_dashboard.php',
        default => 'index.php',
    };
}

function service_catalog(): array
{
    return [
        ['name' => 'Gas', 'description' => 'Gas line installation, burner checks, and urgent leak support.', 'price' => 900.00],
        ['name' => 'Sanitary', 'description' => 'Drain cleaning, pipe repair, and bathroom fixture maintenance.', 'price' => 800.00],
        ['name' => 'Electrical', 'description' => 'Safe wiring, switch repair, and home electrical troubleshooting.', 'price' => 1200.00],
        ['name' => 'Shifting', 'description' => 'Home and office moving support with packing assistance.', 'price' => 2500.00],
        ['name' => 'Locksmith', 'description' => 'Door lock repair, key duplication, and emergency lock opening.', 'price' => 700.00],
    ];
}

function dhaka_area_options(): array
{
    return [
        'Dhanmondi',
        'Lalbagh',
        'Mohammadpur',
        'Mirpur',
        'Uttara',
        'Banani',
        'Gulshan',
        'Badda',
        'Rampura',
        'Malibagh',
        'Bashundhara',
        'Farmgate',
        'Shyamoli',
        'Jatrabari',
        'Motijheel',
        'Paltan',
        'Savar',
        'Keraniganj',
        'Tejgaon',
        'Wari',
    ];
}

function app_url(string $path = ''): string
{
    $base = rtrim(APP_URL, '/');
    $suffix = ltrim($path, '/');
    return $suffix === '' ? $base : $base . '/' . $suffix;
}

function ensure_default_services(): void
{
    $count = (int) pdo()->query('SELECT COUNT(*) FROM services')->fetchColumn();
    if ($count > 0) {
        return;
    }

    $stmt = pdo()->prepare('INSERT INTO services (name, `desc`, price) VALUES (:name, :desc, :price)');
    foreach (service_catalog() as $service) {
        $stmt->execute([
            'name' => $service['name'],
            'desc' => $service['description'],
            'price' => $service['price'],
        ]);
    }
}

function ensure_schema_extensions(): void
{
    $columns = pdo()->query('SHOW COLUMNS FROM users')->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('area', $columns, true)) {
        pdo()->exec('ALTER TABLE users ADD COLUMN area VARCHAR(120) DEFAULT NULL AFTER district');
    }
}

function fetch_services(): array
{
    ensure_default_services();
    return pdo()->query('SELECT id, name, `desc`, price FROM services ORDER BY FIELD(name, "Gas", "Sanitary", "Electrical", "Shifting", "Locksmith"), name')->fetchAll();
}

function fetch_service_by_id(int $serviceId): ?array
{
    ensure_default_services();
    $stmt = pdo()->prepare('SELECT id, name, `desc`, price FROM services WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $serviceId]);
    return $stmt->fetch() ?: null;
}

function fetch_user_location(int $userId): ?array
{
    $stmt = pdo()->prepare('SELECT lat, lng, district, area, postal_code FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $userId]);
    return $stmt->fetch() ?: null;
}

function booking_status_badge(string $status): string
{
    return match ($status) {
        'confirmed' => 'success',
        'completed' => 'primary',
        'awaiting_assignment' => 'primary',
        'cancelled', 'rejected', 'expired' => 'danger',
        default => 'warning',
    };
}

function handle_remembered_login(): void
{
    if (is_logged_in() || empty($_COOKIE['remember_token'])) {
        return;
    }

    $stmt = pdo()->prepare('SELECT id, f_name, l_name, email, role, image, is_verified FROM users WHERE remember_token = :token LIMIT 1');
    $stmt->execute(['token' => hash('sha256', $_COOKIE['remember_token'])]);
    $user = $stmt->fetch();

    if ($user) {
        login_user($user);
        return;
    }

    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
}

function set_remember_token(int $userId): void
{
    $plain = bin2hex(random_bytes(32));
    $hashed = hash('sha256', $plain);
    $stmt = pdo()->prepare('UPDATE users SET remember_token = :token WHERE id = :id');
    $stmt->execute([
        'token' => $hashed,
        'id' => $userId,
    ]);

    setcookie('remember_token', $plain, [
        'expires' => time() + (86400 * 30),
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function clear_remember_token(?int $userId = null): void
{
    if ($userId !== null) {
        $stmt = pdo()->prepare('UPDATE users SET remember_token = NULL WHERE id = :id');
        $stmt->execute(['id' => $userId]);
    }

    setcookie('remember_token', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function save_uploaded_image(array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed. Please try again.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Please upload a JPG, PNG, or WEBP image.');
    }

    $uploadDir = __DIR__ . '/uploads/providers';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
        throw new RuntimeException('Unable to prepare upload directory.');
    }

    $filename = sprintf('provider_%s.%s', bin2hex(random_bytes(12)), $allowed[$mime]);
    $target = $uploadDir . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $target)) {
        throw new RuntimeException('Unable to save the uploaded image.');
    }

    return 'uploads/providers/' . $filename;
}

function haversine_distance(?float $lat1, ?float $lng1, ?float $lat2, ?float $lng2): ?float
{
    if ($lat1 === null || $lng1 === null || $lat2 === null || $lng2 === null) {
        return null;
    }

    $earthRadius = 6371;
    $latDelta = deg2rad($lat2 - $lat1);
    $lngDelta = deg2rad($lng2 - $lng1);
    $a = sin($latDelta / 2) ** 2
        + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lngDelta / 2) ** 2;
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return $earthRadius * $c;
}

function default_avatar(string $label = 'Worker'): string
{
    $svg = sprintf(
        '<svg xmlns="http://www.w3.org/2000/svg" width="280" height="280"><rect width="100%%" height="100%%" rx="32" fill="#EAF2F2"/><text x="50%%" y="52%%" text-anchor="middle" font-family="Arial, sans-serif" font-size="32" fill="#2D3436">%s</text></svg>',
        htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
    );

    return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
}

function ensure_demo_providers(): void
{
    static $seeded = false;
    if ($seeded) {
        return;
    }
    $seeded = true;

    $count = (int) pdo()->query('SELECT COUNT(*) FROM users WHERE role = "provider"')->fetchColumn();
    if ($count > 0) {
        return;
    }

    ensure_default_services();

    $passwordHash = '$2y$10$P8ckJVYuM5ID6g6Q/C37fuUU476UaC3/a7H/epMpv3L8.yr9jtVly';
    $providers = [
        ['f_name' => 'Abdur', 'l_name' => 'Karim', 'email' => 'abdul.karim@utilitylocator.local', 'phone' => '8801700000001', 'postal_code' => '1207', 'district' => 'Dhaka', 'area' => 'Lalbagh', 'lat' => null, 'lng' => null, 'image' => 'uploads/providers/abdul-karim.jpg', 'services' => [['Gas', 950.00]]],
        ['f_name' => 'Rakib', 'l_name' => 'Hasan', 'email' => 'rakib.hasan@utilitylocator.local', 'phone' => '8801700000002', 'postal_code' => '1216', 'district' => 'Dhaka', 'area' => 'Mohammadpur', 'lat' => null, 'lng' => null, 'image' => 'uploads/providers/rakib-hasan.jpg', 'services' => [['Gas', 980.00]]],
        ['f_name' => 'Sharmin', 'l_name' => 'Sultana', 'email' => 'sharmin.sultana@utilitylocator.local', 'phone' => '8801700000003', 'postal_code' => '1212', 'district' => 'Dhaka', 'area' => 'Dhanmondi', 'lat' => null, 'lng' => null, 'image' => 'uploads/providers/sharmin-sultana.jpg', 'services' => [['Sanitary', 820.00]]],
        ['f_name' => 'Tanvir', 'l_name' => 'Ahmed', 'email' => 'tanvir.ahmed@utilitylocator.local', 'phone' => '8801700000004', 'postal_code' => '1229', 'district' => 'Dhaka', 'area' => 'Badda', 'lat' => null, 'lng' => null, 'image' => 'uploads/providers/tanvir-ahmed.jpg', 'services' => [['Electrical', 1250.00]]],
        ['f_name' => 'Nasir', 'l_name' => 'Uddin', 'email' => 'nasir.uddin@utilitylocator.local', 'phone' => '8801700000005', 'postal_code' => '1230', 'district' => 'Dhaka', 'area' => 'Uttara', 'lat' => null, 'lng' => null, 'image' => 'uploads/providers/nasir-uddin.jpg', 'services' => [['Electrical', 1180.00]]],
        ['f_name' => 'Jahidul', 'l_name' => 'Islam', 'email' => 'jahidul.islam@utilitylocator.local', 'phone' => '8801700000006', 'postal_code' => '1205', 'district' => 'Dhaka', 'area' => 'Shyamoli', 'lat' => null, 'lng' => null, 'image' => 'uploads/providers/jahidul-islam.jpg', 'services' => [['Shifting', 2600.00]]],
        ['f_name' => 'Rubel', 'l_name' => 'Mia', 'email' => 'rubel.mia@utilitylocator.local', 'phone' => '8801700000007', 'postal_code' => '1215', 'district' => 'Dhaka', 'area' => 'Malibagh', 'lat' => null, 'lng' => null, 'image' => 'uploads/providers/rubel-mia.jpg', 'services' => [['Shifting', 2450.00]]],
        ['f_name' => 'Farhana', 'l_name' => 'Yasmin', 'email' => 'farhana.yasmin@utilitylocator.local', 'phone' => '8801700000008', 'postal_code' => '1213', 'district' => 'Dhaka', 'area' => 'Rampura', 'lat' => null, 'lng' => null, 'image' => 'uploads/providers/farhana-yasmin.jpg', 'services' => [['Locksmith', 720.00]]],
        ['f_name' => 'Sohel', 'l_name' => 'Rana', 'email' => 'sohel.rana@utilitylocator.local', 'phone' => '8801700000009', 'postal_code' => '1209', 'district' => 'Dhaka', 'area' => 'Mirpur', 'lat' => null, 'lng' => null, 'image' => 'uploads/providers/sohel-rana.jpg', 'services' => [['Sanitary', 840.00]]],
        ['f_name' => 'Mahmud', 'l_name' => 'Hosen', 'email' => 'mahmud.hosen@utilitylocator.local', 'phone' => '8801700000010', 'postal_code' => '1206', 'district' => 'Dhaka', 'area' => 'Farmgate', 'lat' => null, 'lng' => null, 'image' => 'uploads/providers/mahmud-hosen.jpg', 'services' => [['Locksmith', 760.00]]],
    ];

    $serviceMap = [];
    foreach (fetch_services() as $service) {
        $serviceMap[$service['name']] = (int) $service['id'];
    }

    $findUserStmt = pdo()->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $insertUserStmt = pdo()->prepare(
        'INSERT INTO users (f_name, l_name, email, phone, password, role, postal_code, district, area, lat, lng, image, is_verified)
         VALUES (:f_name, :l_name, :email, :phone, :password, "provider", :postal_code, :district, :area, :lat, :lng, :image, 1)'
    );
    $updateUserStmt = pdo()->prepare(
        'UPDATE users
         SET f_name = :f_name,
             l_name = :l_name,
             phone = :phone,
             password = :password,
             role = "provider",
             postal_code = :postal_code,
             district = :district,
             area = :area,
             lat = :lat,
             lng = :lng,
             image = :image,
             is_verified = 1
         WHERE id = :id'
    );
    $providerServiceStmt = pdo()->prepare(
        'INSERT INTO provider_services (provider_id, service_id, custom_price)
         VALUES (:provider_id, :service_id, :custom_price)
         ON DUPLICATE KEY UPDATE custom_price = VALUES(custom_price)'
    );

    foreach ($providers as $provider) {
        $payload = [
            'f_name' => $provider['f_name'],
            'l_name' => $provider['l_name'],
            'email' => $provider['email'],
            'phone' => $provider['phone'],
            'password' => $passwordHash,
            'postal_code' => $provider['postal_code'],
            'district' => $provider['district'],
            'area' => $provider['area'],
            'lat' => $provider['lat'],
            'lng' => $provider['lng'],
            'image' => $provider['image'],
        ];

        $findUserStmt->execute(['email' => $provider['email']]);
        $providerId = (int) ($findUserStmt->fetchColumn() ?: 0);

        if ($providerId > 0) {
            $updateUserStmt->execute([
                'id' => $providerId,
                'f_name' => $payload['f_name'],
                'l_name' => $payload['l_name'],
                'phone' => $payload['phone'],
                'password' => $payload['password'],
                'postal_code' => $payload['postal_code'],
                'district' => $payload['district'],
                'area' => $payload['area'],
                'lat' => $payload['lat'],
                'lng' => $payload['lng'],
                'image' => $payload['image'],
            ]);
        } else {
            $insertUserStmt->execute($payload);
            $providerId = (int) pdo()->lastInsertId();
        }

        foreach ($provider['services'] as [$serviceName, $customPrice]) {
            if (!isset($serviceMap[$serviceName])) {
                continue;
            }

            $providerServiceStmt->execute([
                'provider_id' => $providerId,
                'service_id' => $serviceMap[$serviceName],
                'custom_price' => $customPrice,
            ]);
        }
    }
}

function ensure_admin_user(): void
{
    $email = 'admin@gmail.com';
    
    $stmt = pdo()->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        return;
    }
    
    $insertStmt = pdo()->prepare(
        'INSERT INTO users (f_name, l_name, email, phone, password, role, postal_code, district, area, is_verified)
         VALUES ("System", "Admin", :email, "01700000000", :password, "admin", "1000", "Dhaka", "Dhaka", 1)'
    );
    $insertStmt->execute([
        'email' => $email,
        'password' => password_hash('admin123', PASSWORD_DEFAULT),
    ]);
}

ensure_schema_extensions();
ensure_default_services();
ensure_demo_providers();
ensure_admin_user();
handle_remembered_login();

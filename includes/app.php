<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function app_base_path(): string
{
    $base = getenv('APP_BASE_PATH') ?: '/movie-booking';
    $base = '/' . trim($base, '/');
    return $base === '/' ? '' : $base;
}

function app_url(string $path = ''): string
{
    $path = '/' . ltrim($path, '/');
    return app_base_path() . ($path === '/' ? '' : $path);
}

function redirect(string $path): void
{
    $target = preg_match('/^https?:\/\//i', $path) ? $path : app_url($path);
    header('Location: ' . $target);
    exit;
}

function is_post_request(): bool
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
}

function require_login(): void
{
    if (!isset($_SESSION['user_id'])) {
        redirect('/login.php');
    }
}

function get_positive_int(array $source, string $key): ?int
{
    if (!isset($source[$key])) {
        return null;
    }

    $value = filter_var($source[$key], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $value === false ? null : (int) $value;
}

function normalize_seat_list(array $seats): array
{
    $validSeats = [];

    foreach ($seats as $seat) {
        $seatCode = strtoupper(trim((string) $seat));
        if (preg_match('/^[A-E](?:[1-8])$/', $seatCode) === 1) {
            $validSeats[] = $seatCode;
        }
    }

    $validSeats = array_values(array_unique($validSeats));
    usort($validSeats, static function (string $a, string $b): int {
        $row = strcmp(substr($a, 0, 1), substr($b, 0, 1));
        if ($row !== 0) {
            return $row;
        }

        return ((int) substr($a, 1)) <=> ((int) substr($b, 1));
    });

    return $validSeats;
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function consume_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function json_response(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function cleanup_expired_holds(PDO $pdo): void
{
    $expired = $pdo->prepare("SELECT id FROM bookings WHERE status = 'PENDING' AND expires_at <= NOW() FOR UPDATE");
    $expired->execute();
    $bookingIds = $expired->fetchAll(PDO::FETCH_COLUMN);

    if ($bookingIds === []) {
        return;
    }

    $placeholder = implode(',', array_fill(0, count($bookingIds), '?'));

    $deleteSeats = $pdo->prepare("DELETE FROM booking_seats WHERE booking_id IN ($placeholder)");
    $deleteSeats->execute($bookingIds);

    $cancelBookings = $pdo->prepare("UPDATE bookings SET status = 'CANCELLED' WHERE id IN ($placeholder)");
    $cancelBookings->execute($bookingIds);
}

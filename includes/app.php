<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header("Location: {$path}");
    exit;
}

function is_post_request(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function require_login(): void
{
    if (!isset($_SESSION['user_id'])) {
        redirect('login.php');
    }
}

function get_positive_int(array $source, string $key): ?int
{
    if (!isset($source[$key])) {
        return null;
    }

    $value = filter_var($source[$key], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $value === false ? null : $value;
}

function normalize_seat_list(array $seats): array
{
    $validSeats = [];

    foreach ($seats as $seat) {
        $seatCode = strtoupper(trim((string) $seat));
        if (preg_match('/^[A-D](10|[1-9])$/', $seatCode) === 1) {
            $validSeats[] = $seatCode;
        }
    }

    $validSeats = array_values(array_unique($validSeats));
    sort($validSeats, SORT_NATURAL);

    return $validSeats;
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
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

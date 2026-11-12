<?php
/*
 * helpers.php: small display and navigation helpers shared by the pages.
 */

/* Escape a value before printing it in HTML (prevents cross-site scripting). */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/* 2500 -> "2,500" */
function format_points($points): string
{
    return number_format((int) $points);
}

/* 25 -> "$25.00" */
function format_value($value): string
{
    return '$' . number_format((float) $value, 2);
}

/* 25.0 -> "25.00", for prefilling the Card Value field. */
function value_for_input($value): string
{
    return number_format((float) $value, 2, '.', '');
}

/*
 * The GIFTCARD table has no image column, so the image is chosen by Card Type.
 * Unknown types fall back to the generic gift card image.
 */
function card_image(string $cardType): string
{
    $images = [
        'Retail' => 'images/card-retail.svg',
        'Dining' => 'images/card-dining.svg',
        'Food & Beverage' => 'images/card-food-beverage.svg',
        'Entertainment' => 'images/card-entertainment.svg',
        'Grocery' => 'images/card-grocery.svg',
        'Travel' => 'images/card-travel.svg',
    ];
    return $images[$cardType] ?? 'images/gift-card.png';
}

/* Send the browser to another page after a successful POST (Post/Redirect/Get). */
function redirect(string $url): void
{
    header('Location: ' . $url, true, 303);
    exit;
}

/*
 * Success message after a redirect, chosen by ?msg=<key>.
 * Only keys in this list are shown, so text cannot be injected through the URL.
 */
function status_message(): string
{
    $messages = [
        'added' => 'The gift card was added.',
        'updated' => 'The gift card was updated.',
        'deleted' => 'The gift card was deleted.',
    ];
    $key = $_GET['msg'] ?? '';
    return is_string($key) && isset($messages[$key]) ? $messages[$key] : '';
}

/*
 * Stop and show a message page with the shared templates, for an invalid or
 * missing Gift Card. $status is the HTTP status code (400 or 404).
 */
function show_message_page(int $status, string $heading, string $message): void
{
    http_response_code($status);
    $pageTitle = $heading;
    require_once __DIR__ . '/header.php';
    require_once __DIR__ . '/nav.php';
    echo '<main>';
    echo '<section class="panel">';
    echo '<h1>' . e($heading) . '</h1>';
    echo '<p>' . e($message) . '</p>';
    echo '<div class="button-row"><a class="button" href="card-list.php">Return to Gift Card List</a></div>';
    echo '</section>';
    echo '</main>';
    require_once __DIR__ . '/footer.php';
    exit;
}

/*
 * Unexpected errors (for example, a database failure): write the details to the
 * server log and show the visitor a general message without technical details.
 */
set_exception_handler(function (Throwable $error): void {
    error_log('AirAsia Rewards error: ' . $error->getMessage());
    if (!headers_sent()) {
        http_response_code(500);
    }
    echo '<p role="alert">Something went wrong and the request could not be completed. Please try again.</p>';
    echo '<p><a href="card-list.php">Return to Gift Card List</a></p>';
});

/*
 * Values to show again in the form after a failed submission.
 * Anything that is not plain text (for example, an array sent by an altered
 * request) is replaced with an empty string.
 */
function submitted_values(array $input): array
{
    $values = [];
    foreach (['cardName', 'cardType', 'cardValue', 'points'] as $field) {
        $values[$field] = isset($input[$field]) && is_string($input[$field]) ? $input[$field] : '';
    }
    return $values;
}

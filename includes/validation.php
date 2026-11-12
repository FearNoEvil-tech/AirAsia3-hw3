<?php
/*
 * validation.php: reusable server-side validation for Gift Card data.
 * These functions do not touch the database, so tests/validation-tests.php
 * can call them directly.
 *
 * Rules (from the GIFTCARD table in the requirements document):
 *   cardName   required text, 1 to 100 characters
 *   cardType   one of the allowed types below
 *   cardValue  a positive dollar amount, up to 10,000.00, at most 2 decimals
 *   points     a positive whole number, up to 10,000,000
 *   cardId     a positive whole number
 */

const CARD_NAME_MAX_LENGTH = 100;
const CARD_VALUE_MAX = 10000;
const CARD_POINTS_MAX = 10000000;

/* The only Card Type values the application accepts. */
function allowed_card_types(): array
{
    return ['Retail', 'Dining', 'Food & Beverage', 'Entertainment', 'Grocery', 'Travel'];
}

/* Count characters (not bytes) when the mbstring extension is available. */
function text_length(string $text): int
{
    return function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text);
}

/*
 * Gift Card identifier from a URL or hidden field.
 * Returns the ID as an integer, or null if it is not a positive whole number.
 */
function validate_card_id($value): ?int
{
    if (!is_string($value) && !is_int($value)) {
        return null;
    }
    $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $id === false ? null : $id;
}

/* Each function below returns an error message, or '' when the value is valid. */

function validate_card_name($value): string
{
    if (!is_string($value) || trim($value) === '') {
        return 'Card Name is required.';
    }
    if (text_length(trim($value)) > CARD_NAME_MAX_LENGTH) {
        return 'Card Name must be ' . CARD_NAME_MAX_LENGTH . ' characters or fewer.';
    }
    return '';
}

function validate_card_type($value): string
{
    if (!is_string($value) || !in_array($value, allowed_card_types(), true)) {
        return 'Choose a valid Card Type.';
    }
    return '';
}

function validate_card_value($value): string
{
    $message = 'Card Value must be a number greater than 0, up to ' . number_format(CARD_VALUE_MAX) . ', with no more than 2 decimal places.';

    // Digits with an optional 1 or 2 decimal places. This rejects text, negative
    // signs, and forms such as "1e3" that is_numeric() would accept.
    if (!is_string($value) || !preg_match('/^\d{1,7}(\.\d{1,2})?$/', trim($value))) {
        return $message;
    }
    $number = (float) trim($value);
    if ($number <= 0 || $number > CARD_VALUE_MAX) {
        return $message;
    }
    return '';
}

function validate_card_points($value): string
{
    $message = 'Points Required must be a whole number greater than 0, up to ' . number_format(CARD_POINTS_MAX) . '.';

    if (!is_string($value) || !preg_match('/^\d{1,9}$/', trim($value))) {
        return $message;
    }
    $number = (int) trim($value);
    if ($number < 1 || $number > CARD_POINTS_MAX) {
        return $message;
    }
    return '';
}

/*
 * Validate a submitted Gift Card form (for example, $_POST).
 *
 * Returns an array with:
 *   'errors' => field name => message, for each invalid field (empty when valid)
 *   'data'   => cleaned values ready for the database (only use when there are no errors)
 */
function validate_gift_card(array $input): array
{
    $fields = [
        'cardName' => 'validate_card_name',
        'cardType' => 'validate_card_type',
        'cardValue' => 'validate_card_value',
        'points' => 'validate_card_points',
    ];

    $errors = [];
    foreach ($fields as $field => $validator) {
        $message = $validator($input[$field] ?? null);
        if ($message !== '') {
            $errors[$field] = $message;
        }
    }

    $data = [];
    if ($errors === []) {
        $data = [
            'cardName' => trim($input['cardName']),
            'cardType' => $input['cardType'],
            'cardValue' => round((float) trim($input['cardValue']), 2),
            'points' => (int) trim($input['points']),
        ];
    }

    return ['errors' => $errors, 'data' => $data];
}

<?php
/*
 * validation-tests.php: test harness for includes/validation.php.
 *
 * Open it in the browser (tests/validation-tests.php) or run it in the terminal:
 *     php tests/validation-tests.php
 *
 * It only calls the validation functions. It does not load db.php, so it
 * cannot add, change, or delete database records.
 */
require_once __DIR__ . '/../includes/validation.php';

/* A complete, valid submission. Each test changes one field of it. */
$validCard = [
    'cardName' => 'Bookstore Gift Card',
    'cardType' => 'Retail',
    'cardValue' => '25.00',
    'points' => '2500',
];

$results = [];

/* Show a test input as readable text. */
function describe_input($value): string
{
    if (is_string($value)) {
        $text = strlen($value) > 40 ? substr($value, 0, 24) . '... (' . strlen($value) . ' characters)' : $value;
        return '"' . $text . '"';
    }
    return str_replace("\n", '', var_export($value, true));
}

/*
 * Test the whole form with validate_gift_card().
 * $changes replaces fields of the valid card. $invalidField is the one field
 * expected to fail, or null when the data should pass.
 */
function test_form(string $name, array $changes, ?string $invalidField): void
{
    global $results, $validCard;

    $result = validate_gift_card(array_merge($validCard, $changes));
    $failedFields = array_keys($result['errors']);

    if ($invalidField === null) {
        $expected = 'Passes validation';
        $ok = $failedFields === [];
    } else {
        $expected = 'Fails validation (' . $invalidField . ')';
        $ok = $failedFields === [$invalidField];   // that field fails, and only that field
    }

    $actual = $failedFields === []
        ? 'Passed validation'
        : 'Failed validation: ' . implode(' ', $result['errors']);

    $inputs = [];
    foreach ($changes as $field => $value) {
        $inputs[] = $field . ' = ' . describe_input($value);
    }

    $results[] = [
        'name' => $name,
        'input' => $inputs === [] ? 'Valid card (no changes)' : implode(', ', $inputs),
        'expected' => $expected,
        'actual' => $actual,
        'ok' => $ok,
    ];
}

/* Test a single value against an expected result with ===. */
function test_equals(string $name, string $input, $expected, $actual): void
{
    global $results;
    $results[] = [
        'name' => $name,
        'input' => $input,
        'expected' => describe_input($expected),
        'actual' => describe_input($actual),
        'ok' => $expected === $actual,
    ];
}

/* ---- Required tests ---- */
test_form('1. Valid Gift Card data', [], null);
test_form('2. Blank Card Name', ['cardName' => ''], 'cardName');
test_form('3. Altered or invalid Card Type', ['cardType' => 'Free Money'], 'cardType');
test_form('4a. Zero Card Value', ['cardValue' => '0'], 'cardValue');
test_form('4b. Negative Card Value', ['cardValue' => '-25.00'], 'cardValue');
test_form('5a. Invalid Points Required (text)', ['points' => 'abc'], 'points');
test_form('5b. Invalid Points Required (zero)', ['points' => '0'], 'points');
test_form('5c. Invalid Points Required (negative)', ['points' => '-100'], 'points');
test_form('5d. Invalid Points Required (decimal)', ['points' => '2500.5'], 'points');

/* ---- Card Name ---- */
test_form('Card Name with only spaces', ['cardName' => '     '], 'cardName');
test_form('Card Name with 100 characters (limit)', ['cardName' => str_repeat('A', 100)], null);
test_form('Card Name with 101 characters', ['cardName' => str_repeat('A', 101)], 'cardName');
test_form('Card Name sent as a list (altered request)', ['cardName' => ['Bookstore']], 'cardName');

/* ---- Card Type ---- */
test_form('Card Type not selected', ['cardType' => ''], 'cardType');
test_form('Card Type in the wrong case', ['cardType' => 'retail'], 'cardType');
test_form('Card Type "Food & Beverage" (allowed)', ['cardType' => 'Food & Beverage'], null);

/* ---- Card Value ---- */
test_form('Card Value as text', ['cardValue' => 'twenty'], 'cardValue');
test_form('Card Value in scientific notation', ['cardValue' => '1e3'], 'cardValue');
test_form('Card Value with 3 decimal places', ['cardValue' => '12.345'], 'cardValue');
test_form('Card Value blank', ['cardValue' => ''], 'cardValue');
test_form('Card Value 0.01 (smallest allowed)', ['cardValue' => '0.01'], null);
test_form('Card Value 10000 (largest allowed)', ['cardValue' => '10000'], null);
test_form('Card Value 10000.01 (over the limit)', ['cardValue' => '10000.01'], 'cardValue');

/* ---- Points Required ---- */
test_form('Points Required blank', ['points' => ''], 'points');
test_form('Points Required 1 (smallest allowed)', ['points' => '1'], null);
test_form('Points Required 10000000 (largest allowed)', ['points' => '10000000'], null);
test_form('Points Required 10000001 (over the limit)', ['points' => '10000001'], 'points');

/* ---- Cleaned data returned for a valid card ---- */
$clean = validate_gift_card(['cardName' => '  Bookstore Gift Card  ', 'cardType' => 'Retail', 'cardValue' => '25', 'points' => '2500']);
test_equals('Valid data: Card Name is trimmed', 'cardName = "  Bookstore Gift Card  "', 'Bookstore Gift Card', $clean['data']['cardName'] ?? null);
test_equals('Valid data: Card Value becomes a number', 'cardValue = "25"', 25.0, $clean['data']['cardValue'] ?? null);
test_equals('Valid data: Points Required becomes a whole number', 'points = "2500"', 2500, $clean['data']['points'] ?? null);

/* ---- Gift Card identifiers (card_id) ---- */
test_equals('Card ID "12" is accepted', 'card_id = "12"', 12, validate_card_id('12'));
test_equals('Card ID text is rejected', 'card_id = "abc"', null, validate_card_id('abc'));
test_equals('Card ID zero is rejected', 'card_id = "0"', null, validate_card_id('0'));
test_equals('Card ID negative is rejected', 'card_id = "-3"', null, validate_card_id('-3'));
test_equals('Card ID decimal is rejected', 'card_id = "1.5"', null, validate_card_id('1.5'));
test_equals('Card ID with SQL text is rejected', 'card_id = "1 OR 1=1"', null, validate_card_id('1 OR 1=1'));
test_equals('Card ID missing is rejected', 'card_id not sent', null, validate_card_id(null));
test_equals('Card ID sent as a list is rejected', 'card_id[] = "1"', null, validate_card_id(['1']));

/* ---- Results ---- */
$total = count($results);
$failed = count(array_filter($results, function ($r) { return !$r['ok']; }));
$summary = $failed === 0
    ? "All $total validation tests passed."
    : "$failed of $total validation tests FAILED.";

// Terminal: plain text, with exit code 1 if any test failed.
if (PHP_SAPI === 'cli') {
    foreach ($results as $r) {
        echo ($r['ok'] ? 'PASS  ' : 'FAIL  ') . $r['name'] . PHP_EOL;
        if (!$r['ok']) {
            echo '      expected: ' . $r['expected'] . PHP_EOL . '      actual:   ' . $r['actual'] . PHP_EOL;
        }
    }
    echo $summary . PHP_EOL;
    exit($failed === 0 ? 0 : 1);
}

// Browser: a table. Every value is escaped before it is displayed.
function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Validation Tests | AirAsia Rewards</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>

<body>
    <main>
        <a class="back-link" href="../card-list.php">&larr; Return to Gift Card List</a>
        <h1>Gift Card Validation Tests</h1>
        <p class="alert <?= $failed === 0 ? 'alert-success' : 'alert-error' ?>" role="status"><strong><?= h($summary) ?></strong>
            These tests call the functions in <code>includes/validation.php</code> only. No database records are read or changed.</p>

        <div class="table-scroll">
        <table class="test-table">
            <thead>
                <tr>
                    <th scope="col">Test</th>
                    <th scope="col">Input</th>
                    <th scope="col">Expected result</th>
                    <th scope="col">Actual result</th>
                    <th scope="col">Result</th>
                </tr>
            </thead>
            <tbody>
<?php foreach ($results as $r): ?>
                <tr>
                    <th scope="row"><?= h($r['name']) ?></th>
                    <td><?= h($r['input']) ?></td>
                    <td><?= h($r['expected']) ?></td>
                    <td><?= h($r['actual']) ?></td>
                    <td class="<?= $r['ok'] ? 'result-pass' : 'result-fail' ?>"><?= $r['ok'] ? 'PASS' : 'FAIL' ?></td>
                </tr>
<?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </main>
</body>

</html>

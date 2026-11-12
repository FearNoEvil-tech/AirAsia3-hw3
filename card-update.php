<?php
/*
 * card-update.php: Update a Gift Card (use case 5).
 * GET  card-update.php?card_id=12 shows the form filled with the current values.
 * POST validates on the server, updates the row with a prepared statement, and
 * shows the updated card on card-details.php.
 */
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/validation.php';
require_once __DIR__ . '/includes/giftcards.php';

$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';

// The ID comes from the URL when the form is opened and from the hidden field
// when it is submitted. Either way it is validated before any query.
$cardId = validate_card_id($isPost ? ($_POST['card_id'] ?? null) : ($_GET['card_id'] ?? null));
if ($cardId === null) {
    show_message_page(400, 'Invalid gift card', 'The gift card link is not valid. Choose a gift card from the list.');
}

$pdo = get_db();
$card = find_card($pdo, $cardId);
if ($card === null) {
    show_message_page(404, 'Gift card not found', 'That gift card does not exist. It may have been deleted.');
}

// Start with the values stored in the database.
$values = [
    'cardName' => $card['cardName'],
    'cardType' => $card['cardType'],
    'cardValue' => value_for_input($card['cardValue']),
    'points' => (string) $card['points'],
];
$errors = [];

if ($isPost) {
    $result = validate_gift_card($_POST);
    $errors = $result['errors'];

    if ($errors === []) {
        update_card($pdo, $cardId, $result['data']);
        redirect('card-details.php?card_id=' . $cardId . '&msg=updated');
    }

    // Validation failed: nothing was saved. Show what the user typed.
    http_response_code(422);
    $values = submitted_values($_POST);
}

$pageTitle = 'Update Gift Card';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/nav.php';
?>
    <main>
        <a class="back-link" href="card-details.php?card_id=<?= $cardId ?>">&larr; Back to Gift Card Details</a>

        <section class="panel form-panel" aria-labelledby="update-heading">
            <h1 id="update-heading">Update Gift Card</h1>
            <p class="required-note">Editing Card ID <?= $cardId ?>: <?= e($card['cardName']) ?>. All fields are required.</p>

            <form action="card-update.php" method="post">
                <!-- Identifies the record being edited. It is validated again on the server. -->
                <input type="hidden" name="card_id" value="<?= $cardId ?>">

<?php require __DIR__ . '/includes/card-form.php'; ?>

                <div class="button-row">
                    <button type="submit" class="button">Save Changes</button>
                    <a class="button button-secondary" href="card-details.php?card_id=<?= $cardId ?>">Cancel</a>
                </div>
            </form>
        </section>
    </main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

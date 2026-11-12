<?php
/*
 * card-add.php: Add a Gift Card (use case 4).
 * GET shows an empty form. POST validates on the server, inserts the card with
 * a prepared statement, and returns to the Gift Card List.
 */
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/validation.php';
require_once __DIR__ . '/includes/giftcards.php';

$values = ['cardName' => '', 'cardType' => '', 'cardValue' => '', 'points' => ''];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = validate_gift_card($_POST);
    $errors = $result['errors'];

    if ($errors === []) {
        insert_card(get_db(), $result['data']);
        redirect('card-list.php?msg=added');
    }

    // Validation failed: show the form again with what the user typed.
    http_response_code(422);
    $values = submitted_values($_POST);
}

$pageTitle = 'Add Gift Card';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/nav.php';
?>
    <main>
        <a class="back-link" href="card-list.php">&larr; Return to Gift Card List</a>

        <section class="panel form-panel" aria-labelledby="add-heading">
            <h1 id="add-heading">Add a New Gift Card</h1>
            <p class="required-note">All fields are required. The Card ID is generated automatically.</p>

            <form action="card-add.php" method="post">
<?php require __DIR__ . '/includes/card-form.php'; ?>

                <div class="button-row">
                    <button type="submit" class="button">Add Gift Card</button>
                    <a class="button button-secondary" href="card-list.php">Cancel</a>
                </div>
            </form>
        </section>
    </main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

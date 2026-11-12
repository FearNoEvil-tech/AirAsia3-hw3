<?php
/*
 * card-delete.php: Delete a Gift Card (use case 6).
 * GET  card-delete.php?card_id=12 shows the card and asks for confirmation.
 *      Opening this page never deletes anything.
 * POST (the confirmation form) deletes the row and returns to the Gift Card List.
 */
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/validation.php';
require_once __DIR__ . '/includes/giftcards.php';

$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';

$cardId = validate_card_id($isPost ? ($_POST['card_id'] ?? null) : ($_GET['card_id'] ?? null));
if ($cardId === null) {
    show_message_page(400, 'Invalid gift card', 'The gift card link is not valid. Choose a gift card from the list.');
}

$pdo = get_db();
$card = find_card($pdo, $cardId);
if ($card === null) {
    show_message_page(404, 'Gift card not found', 'That gift card does not exist. It may have already been deleted.');
}

// Delete only after the user confirms through the POST form.
if ($isPost && ($_POST['confirm'] ?? '') === 'yes') {
    delete_card($pdo, $cardId);
    redirect('card-list.php?msg=deleted');
}

$pageTitle = 'Delete Gift Card';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/nav.php';
?>
    <main>
        <a class="back-link" href="card-details.php?card_id=<?= $cardId ?>">&larr; Back to Gift Card Details</a>

        <section class="panel" aria-labelledby="delete-heading">
            <h1 id="delete-heading">Delete Gift Card</h1>
            <p class="alert alert-error" role="alert">
                <strong>Delete &ldquo;<?= e($card['cardName']) ?>&rdquo; (Card ID <?= $cardId ?>)?</strong>
                This permanently removes the gift card and cannot be undone.
            </p>

            <div class="details">
                <img src="<?= e(card_image($card['cardType'])) ?>" alt="<?= e($card['cardType']) ?> gift card" width="800" height="500">

                <dl>
                    <dt>Card ID</dt>
                    <dd><?= $cardId ?></dd>

                    <dt>Card Name</dt>
                    <dd><?= e($card['cardName']) ?></dd>

                    <dt>Card Type</dt>
                    <dd><?= e($card['cardType']) ?></dd>

                    <dt>Card Value</dt>
                    <dd><?= e(format_value($card['cardValue'])) ?></dd>

                    <dt>Points Required</dt>
                    <dd><?= e(format_points($card['points'])) ?></dd>
                </dl>
            </div>

            <form action="card-delete.php" method="post">
                <input type="hidden" name="card_id" value="<?= $cardId ?>">
                <input type="hidden" name="confirm" value="yes">

                <div class="button-row">
                    <button type="submit" class="button button-danger">Yes, Delete Gift Card</button>
                    <a class="button button-secondary" href="card-details.php?card_id=<?= $cardId ?>">Cancel</a>
                </div>
            </form>
        </section>
    </main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

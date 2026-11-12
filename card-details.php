<?php
/*
 * card-details.php: View Details of One Gift Card (use case 3).
 * Example: card-details.php?card_id=12
 */
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/validation.php';
require_once __DIR__ . '/includes/giftcards.php';

// Validate the identifier before it is used in a query.
$cardId = validate_card_id($_GET['card_id'] ?? null);
if ($cardId === null) {
    show_message_page(400, 'Invalid gift card', 'The gift card link is not valid. Choose a gift card from the list.');
}

$card = find_card(get_db(), $cardId);
if ($card === null) {
    show_message_page(404, 'Gift card not found', 'That gift card does not exist. It may have been deleted.');
}

$message = status_message();

$pageTitle = 'Gift Card Details';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/nav.php';
?>
    <main>
        <a class="back-link" href="card-list.php">&larr; Return to Gift Card List</a>

<?php if ($message !== ''): ?>
        <p class="alert alert-success" role="status"><?= e($message) ?></p>
<?php endif; ?>

        <article class="panel">
            <h1><?= e($card['cardName']) ?></h1>

            <!-- Read-only page: values are plain text, not form fields -->
            <div class="details">
                <img src="<?= e(card_image($card['cardType'])) ?>" alt="<?= e($card['cardType']) ?> gift card" width="800" height="500">

                <dl>
                    <dt>Card ID</dt>
                    <dd><?= (int) $card['cardId'] ?></dd>

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

            <div class="button-row">
                <a class="button" href="card-update.php?card_id=<?= (int) $card['cardId'] ?>">Update Gift Card</a>
                <a class="button button-danger" href="card-delete.php?card_id=<?= (int) $card['cardId'] ?>">Delete Gift Card</a>
                <a class="button button-secondary" href="card-list.php">Return to Gift Card List</a>
            </div>
        </article>
    </main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

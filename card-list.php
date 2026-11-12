<?php
/*
 * card-list.php: View a List of Gift Cards (use case 2).
 * Reads every GIFTCARD row and links each card to its details page.
 */
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/giftcards.php';

$cards = find_all_cards(get_db());
$message = status_message();

$pageTitle = 'Gift Card List';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/nav.php';
?>
    <main>
        <div class="list-header">
            <h1>Available Gift Cards</h1>
            <a class="button" href="card-add.php">Add Gift Card</a>
        </div>

<?php if ($message !== ''): ?>
        <p class="alert alert-success" role="status"><?= e($message) ?></p>
<?php endif; ?>

<?php if ($cards === []): ?>
        <p class="panel">There are no gift cards yet. <a href="card-add.php">Add the first gift card</a>.</p>
<?php else: ?>
        <p class="page-intro"><?= count($cards) ?> gift cards. Select a card to view its details.</p>

        <ul class="card-grid">
<?php foreach ($cards as $card): ?>
<?php $detailsUrl = 'card-details.php?card_id=' . (int) $card['cardId']; ?>
            <li class="gift-card">
                <a href="<?= e($detailsUrl) ?>"><img src="<?= e(card_image($card['cardType'])) ?>" alt="<?= e($card['cardName']) ?>" width="800" height="500"></a>
                <h2><a href="<?= e($detailsUrl) ?>"><?= e($card['cardName']) ?></a></h2>
                <p class="points">Points required: <strong><?= e(format_points($card['points'])) ?></strong></p>
            </li>
<?php endforeach; ?>
        </ul>
<?php endif; ?>
    </main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php
/*
 * giftcards.php: every SQL statement for the GIFTCARD table.
 * Pages call these functions, so SQL lives in one file. Any value that comes
 * from a form or the URL is passed as a bound parameter, never placed in the
 * SQL text.
 */

/* Read: all gift cards for card-list.php. */
function find_all_cards(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT cardId, cardName, cardType, cardValue, points FROM GIFTCARD ORDER BY cardName, cardId');
    return $stmt->fetchAll();
}

/* Read: one gift card by its ID, or null if it does not exist. */
function find_card(PDO $pdo, int $cardId): ?array
{
    $stmt = $pdo->prepare('SELECT cardId, cardName, cardType, cardValue, points FROM GIFTCARD WHERE cardId = :cardId');
    $stmt->execute([':cardId' => $cardId]);
    $card = $stmt->fetch();
    return $card === false ? null : $card;
}

/* Create: insert a validated gift card and return its new cardId. */
function insert_card(PDO $pdo, array $card): int
{
    $stmt = $pdo->prepare(
        'INSERT INTO GIFTCARD (cardName, cardType, cardValue, points)
         VALUES (:cardName, :cardType, :cardValue, :points)'
    );
    $stmt->execute([
        ':cardName' => $card['cardName'],
        ':cardType' => $card['cardType'],
        ':cardValue' => $card['cardValue'],
        ':points' => $card['points'],
    ]);
    return (int) $pdo->lastInsertId();
}

/* Update: change one gift card. Returns true if a row was updated. */
function update_card(PDO $pdo, int $cardId, array $card): bool
{
    $stmt = $pdo->prepare(
        'UPDATE GIFTCARD
         SET cardName = :cardName, cardType = :cardType, cardValue = :cardValue, points = :points
         WHERE cardId = :cardId'
    );
    $stmt->execute([
        ':cardName' => $card['cardName'],
        ':cardType' => $card['cardType'],
        ':cardValue' => $card['cardValue'],
        ':points' => $card['points'],
        ':cardId' => $cardId,
    ]);
    return $stmt->rowCount() === 1;
}

/* Delete: remove one gift card. Returns true if a row was deleted. */
function delete_card(PDO $pdo, int $cardId): bool
{
    $stmt = $pdo->prepare('DELETE FROM GIFTCARD WHERE cardId = :cardId');
    $stmt->execute([':cardId' => $cardId]);
    return $stmt->rowCount() === 1;
}

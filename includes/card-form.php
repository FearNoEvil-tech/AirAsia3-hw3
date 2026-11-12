<?php
/*
 * card-form.php: the Gift Card fields shared by card-add.php and card-update.php.
 * Set before including:
 *   $values  field name => value to show in each control
 *   $errors  field name => validation message (empty array when there are none)
 * The Card ID is never an editable field.
 */
if (!isset($values, $errors)) {
    http_response_code(404);   // opened directly instead of from a form page
    exit;
}

$fieldError = function (string $field) use ($errors): string {
    if (!isset($errors[$field])) {
        return '';
    }
    return '<p class="field-error" id="' . e($field) . '-error">' . e($errors[$field]) . '</p>';
};
$fieldAttributes = function (string $field) use ($errors): string {
    return isset($errors[$field]) ? ' aria-invalid="true" aria-describedby="' . e($field) . '-error"' : '';
};
?>
<?php if ($errors !== []): ?>
                <div class="alert alert-error" role="alert">
                    <p><strong>The gift card was not saved.</strong> Please correct the following:</p>
                    <ul>
<?php foreach ($errors as $message): ?>
                        <li><?= e($message) ?></li>
<?php endforeach; ?>
                    </ul>
                </div>
<?php endif; ?>

                <div class="form-group">
                    <label for="cardName">Card Name</label>
                    <input type="text" id="cardName" name="cardName" maxlength="100"
                        value="<?= e($values['cardName']) ?>" placeholder="e.g., Bookstore Gift Card" required<?= $fieldAttributes('cardName') ?>>
                    <?= $fieldError('cardName') ?>
                </div>

                <div class="form-group">
                    <label for="cardType">Card Type</label>
                    <select id="cardType" name="cardType" required<?= $fieldAttributes('cardType') ?>>
                        <option value="">-- Select a card type --</option>
<?php foreach (allowed_card_types() as $type): ?>
                        <option value="<?= e($type) ?>"<?= $values['cardType'] === $type ? ' selected' : '' ?>><?= e($type) ?></option>
<?php endforeach; ?>
                    </select>
                    <?= $fieldError('cardType') ?>
                </div>

                <div class="form-group">
                    <label for="cardValue">Card Value ($)</label>
                    <input type="number" id="cardValue" name="cardValue" min="0.01" max="10000" step="0.01"
                        value="<?= e($values['cardValue']) ?>" placeholder="25.00" required<?= $fieldAttributes('cardValue') ?>>
                    <?= $fieldError('cardValue') ?>
                </div>

                <div class="form-group">
                    <label for="points">Points Required</label>
                    <input type="number" id="points" name="points" min="1" max="10000000" step="1"
                        value="<?= e($values['points']) ?>" placeholder="2500" required<?= $fieldAttributes('points') ?>>
                    <?= $fieldError('points') ?>
                </div>

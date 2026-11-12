<?php
/*
 * nav.php: site header bar and main navigation, shared by every page.
 * The link for the current page is marked with aria-current="page".
 */
require_once __DIR__ . '/helpers.php';

$navLinks = [
    'card-list.php' => 'Gift Card List',
    'card-add.php' => 'Add Gift Card',
];
$currentPage = basename($_SERVER['SCRIPT_NAME'] ?? '');
?>
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="card-list.php">AirAsia Rewards</a>
            <nav class="main-nav" aria-label="Main navigation">
                <ul>
<?php foreach ($navLinks as $href => $label): ?>
                    <li><a href="<?= e($href) ?>"<?= $href === $currentPage ? ' aria-current="page"' : '' ?>><?= e($label) ?></a></li>
<?php endforeach; ?>
                </ul>
            </nav>
        </div>
    </header>

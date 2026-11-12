<?php
/*
 * header.php: document setup and stylesheet link, shared by every page.
 * Set $pageTitle before including this file.
 */
require_once __DIR__ . '/helpers.php';
$pageTitle = $pageTitle ?? 'Gift Cards';
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | AirAsia Rewards</title>
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>

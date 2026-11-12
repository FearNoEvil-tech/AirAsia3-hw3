<?php
/*
 * db.php: the one place that opens the PDO connection to SQLite.
 * Every page calls get_db() instead of creating its own connection.
 */

function get_db(): PDO
{
    static $pdo = null;   // reuse the same connection for the whole request

    if ($pdo === null) {
        $path = __DIR__ . '/../database/rewards.db';

        // PDO would silently create an empty file, so check that the database exists.
        if (!is_file($path)) {
            throw new RuntimeException('Database file not found. Build it from database/schema.sql and database/seed.sql.');
        }

        $pdo = new PDO('sqlite:' . $path);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);          // errors become exceptions
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);     // rows as column => value
        $pdo->exec('PRAGMA foreign_keys = ON');                                 // SQLite leaves this off by default
    }

    return $pdo;
}

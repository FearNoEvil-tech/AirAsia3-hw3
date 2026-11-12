-- AirAsia Rewards Redemption System: HW3 database schema (SQLite)
--
-- Table names, column names, and attributes follow the AirAsia Requirements
-- Document. HW3 needs the ACCOUNT and GIFTCARD tables. The USER and REDEMPTION
-- tables from the ERD are added in HW4 with login and redemption.
--
-- Build the database from the project root:
--     sqlite3 database/rewards.db < database/schema.sql
--     sqlite3 database/rewards.db < database/seed.sql
--
-- This script drops both tables first, so running it again gives a clean start.

PRAGMA foreign_keys = ON;

DROP TABLE IF EXISTS GIFTCARD;
DROP TABLE IF EXISTS ACCOUNT;

-- ACCOUNT: one rewards account per user, holding the user's point balance.
-- Relationship (ERD): USER 1-to-many ACCOUNT through userId, and
-- ACCOUNT 1-to-many REDEMPTION through accountId. The FOREIGN KEY on userId
-- is added in HW4, when the USER table exists; SQLite rejects inserts that
-- reference a table that has not been created.
CREATE TABLE ACCOUNT (
    accountId   INTEGER PRIMARY KEY AUTOINCREMENT,   -- int(11), auto, primary key
    userId      INTEGER NOT NULL,                    -- int(11)
    accountType VARCHAR(100) NOT NULL,
    points      INTEGER NOT NULL                     -- int(50)
                CHECK (points >= 0)
);

-- GIFTCARD: the gift cards customers can redeem with points.
-- Relationship (ERD): GIFTCARD 1-to-many REDEMPTION through cardId (HW4).
CREATE TABLE GIFTCARD (
    cardId    INTEGER PRIMARY KEY AUTOINCREMENT,     -- int(11), auto, primary key
    cardName  VARCHAR(100) NOT NULL
              CHECK (length(trim(cardName)) BETWEEN 1 AND 100),
    cardType  VARCHAR(100) NOT NULL
              CHECK (length(trim(cardType)) BETWEEN 1 AND 100),
    cardValue FLOAT NOT NULL                         -- float(10), dollars
              CHECK (cardValue > 0),
    points    INTEGER NOT NULL                       -- int(50), points required
              CHECK (points > 0)
);

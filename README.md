# AirAsia Rewards: Gift Card Management (HW3)

Cody Osmond, IS 6465

A PHP application that manages the gift cards in the AirAsia Rewards Redemption System. It stores data in SQLite through PDO and supports the full Gift Card workflow: list, view details, add, update, and delete. HW3 covers Gift Card management only. Login, sessions, authorization, customer management, and redemption are HW4.

## Run it

In GitHub Codespaces, from the project folder:

```bash
php -S 0.0.0.0:8000 router.php
```

Open port 8000. The site starts at the Gift Card List (`card-list.php`).

`router.php` is optional but recommended: without it, the development server lets anyone download `database/rewards.db` from the browser. `php -S 0.0.0.0:8000` also runs the application.

**Validation tests:** open `tests/validation-tests.php` in the browser, or run `php tests/validation-tests.php`.

**Rebuild the database** (restores the original 5 accounts and 20 gift cards):

```bash
sqlite3 database/rewards.db < database/schema.sql
sqlite3 database/rewards.db < database/seed.sql
```

If `sqlite3` is not installed in the Codespace, install it with `sudo apt-get update && sudo apt-get install -y sqlite3`. The application itself only needs PHP with the PDO SQLite extension.

## Project organization

```
card-list.php          View a List of Gift Cards
card-details.php       View Details of One Gift Card (card-details.php?card_id=12)
card-add.php           Add a Gift Card
card-update.php        Update a Gift Card
card-delete.php        Delete a Gift Card (confirmation page, POST only)
index.php              Sends the site root to card-list.php
router.php             Optional development-server router that blocks database/ and includes/
includes/
  header.php           Document setup and stylesheet link
  nav.php              Site header and navigation (Gift Card List, Add Gift Card)
  footer.php           Common closing content
  db.php               The PDO connection to SQLite (get_db)
  validation.php       Reusable server-side validation functions
  giftcards.php        Every SQL statement for the GIFTCARD table
  helpers.php          Output escaping, formatting, redirects, and message pages
  card-form.php        Form fields shared by the Add and Update pages
database/
  schema.sql           Creates the ACCOUNT and GIFTCARD tables
  seed.sql             Inserts 5 ACCOUNT rows and 20 GIFTCARD rows
  rewards.db           The database built from the two files above
tests/
  validation-tests.php Validation test harness (PASS or FAIL for each test)
css/styles.css         Shared stylesheet
images/                Gift card images, one per Card Type
test-report.md         Manual test results, T01 to T08
ai-use.txt             AI use statement
```

## Page workflow

```
card-list.php
   ├── View details → card-details.php?card_id=...
   └── Add Gift Card → card-add.php

card-details.php
   ├── Update Gift Card → card-update.php?card_id=...
   └── Delete Gift Card → card-delete.php?card_id=...

card-add.php     successful insert → card-list.php
card-update.php  successful update → card-details.php?card_id=...
card-delete.php  confirmed deletion → card-list.php
```

## Database

Table names, column names, and attributes follow the AirAsia Requirements Document.

| Table | Columns |
|---|---|
| `ACCOUNT` | `accountId` (integer, auto, primary key), `userId` (integer), `accountType` (varchar 100), `points` (integer). All columns are NOT NULL. |
| `GIFTCARD` | `cardId` (integer, auto, primary key), `cardName` (varchar 100), `cardType` (varchar 100), `cardValue` (float), `points` (integer). All columns are NOT NULL. |

- `ACCOUNT` is created and seeded for HW4. HW3 has no ACCOUNT pages.
- In the ERD, the two tables are related through the `USER` and `REDEMPTION` tables, which belong to HW4. `ACCOUNT.userId` is stored now, and its foreign key to `USER` will be added when the `USER` table is created.
- The tables also have `CHECK` constraints (Card Value and Points Required above zero, names not blank) as a second line of defense behind the PHP validation.
- `cardId` uses `AUTOINCREMENT`, so the ID of a deleted card is never given to a new card.
- The `GIFTCARD` table has no image column, so each card's image is chosen by its Card Type (`card_image()` in `includes/helpers.php`).

## Server-side validation

`includes/validation.php` checks every submission before an insert or update. HTML controls (`required`, the dropdown, number limits) help the user, but the server does not rely on them.

| Value | Rule |
|---|---|
| Card Name | Required; 1 to 100 characters after trimming spaces |
| Card Type | Must be exactly one of: Retail, Dining, Food & Beverage, Entertainment, Grocery, Travel |
| Card Value | A number greater than 0, up to 10,000, with no more than 2 decimal places |
| Points Required | A whole number greater than 0, up to 10,000,000 |
| Card ID (`card_id`) | A whole number of 1 or more; checked before any query |

Safe output and queries:

- Every value from a form or the database is displayed through `e()`, which calls `htmlspecialchars()`.
- Every query that receives a form or URL value is a PDO prepared statement with named parameters.
- Add, update, and delete use POST and then redirect, so reloading a page cannot repeat the action. Delete never runs from a GET link.
- An invalid `card_id` shows an "Invalid gift card" page (HTTP 400), and an ID with no matching card shows "Gift card not found" (HTTP 404).

## Use Case Traceability

Use case names are from the AirAsia Requirements Document.

| Use case | PHP page or pages | Database table | Test evidence |
|---|---|---|---|
| View a list of gift cards | `card-list.php` | GIFTCARD | Opened the list and confirmed all 20 seeded gift cards appeared with an image, Card Name, and Points Required, each linked to its details page (T02). |
| View details of one gift card | `card-details.php` | GIFTCARD | Selected Bookstore Gift Card and confirmed `card-details.php?card_id=1` showed its ID, name, type, value, and points as read-only text with Update, Delete, and Return links (T03). |
| Add data for a new gift card | `card-add.php` | GIFTCARD | Submitted valid Gift Card data (Ski Resort Gift Card) and confirmed the new record appeared on `card-list.php` and in the GIFTCARD table (T04). |
| Update data for one gift card | `card-update.php`, `card-details.php` | GIFTCARD | Changed all four values of Card ID 21 in the prefilled form and confirmed `card-details.php` and the GIFTCARD table showed the new values (T05). |
| Delete a card | `card-delete.php`, `card-list.php` | GIFTCARD | Confirmed that opening the delete page removed nothing, then confirmed deletion through the POST form and verified the card was gone from `card-list.php` and the GIFTCARD table (T06). |

Full steps and results are in [test-report.md](test-report.md).

## Where HW3 differs from the requirements document

The requirements document is the source for table names, column names, attributes, and business rules. In two places, the HW3 instructions set a more specific page flow, and this project follows HW3:

- **Update.** The requirements document forwards the user to `card-list` after an update. HW3 requires the updated information to be displayed, so a successful update goes to `card-details.php?card_id=...`.
- **Delete.** The requirements document describes `card-delete` as a PHP page with no HTML display. HW3 requires a page that identifies the card and asks for confirmation through a POST form.

The requirements document also lists a link to `cust-add` on the card list and assigns roles to each use case. Both are part of HW4 and are not in this project.

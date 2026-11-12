# HW3 Test Report: AirAsia Rewards Gift Card Management

Cody Osmond, IS 6465

- **Date tested:** November 11, 2026
- **Environment:** PHP 8.3.6 development server (`php -S`), SQLite 3.45.1, Chromium browser
- **Starting data:** `database/rewards.db` built from `schema.sql` and `seed.sql` (5 ACCOUNT rows, 20 GIFTCARD rows)
- **Result:** all 8 tests passed. One defect was found and corrected (see [Defect found and corrected](#defect-found-and-corrected)).

After each test that changes data, the database was also checked directly with `sqlite3 database/rewards.db`, so the results below reflect what was stored, not only what the page displayed. The database was rebuilt from the schema and seed files after testing, so the repository copy has the original 20 gift cards.

## Summary

| Test ID | Test | Status |
|---|---|---|
| T01 | Validation test harness | Pass |
| T02 | View Gift Card List | Pass |
| T03 | View Gift Card Details | Pass |
| T04 | Add Gift Card | Pass |
| T05 | Update Gift Card | Pass |
| T06 | Delete Gift Card | Pass |
| T07 | Invalid or altered input handling | Pass |
| T08 | Invalid Gift Card identifier handling | Pass |

## T01: Validation test harness

**Steps and input:**
1. Open `tests/validation-tests.php` in the browser.
2. Run `php tests/validation-tests.php` in the terminal.
3. Count the rows in both tables before and after.

**Expected result:** Every test shows PASS, including the five required tests (valid data, blank Card Name, altered Card Type, zero or negative Card Value, invalid Points Required). No database records are added, changed, or deleted.

**Actual result:** The page reported "All 38 validation tests passed," with 38 PASS rows and 0 FAIL rows. The terminal run printed the same 38 PASS lines and ended with exit code 0. The tables had 20 GIFTCARD rows and 5 ACCOUNT rows before and after. The test file does not load `includes/db.php`, so it has no database connection.

**Status:** Pass

## T02: View Gift Card List

**Steps and input:**
1. Open the site root (`/`).
2. Review the cards, links, and navigation on `card-list.php`.

**Expected result:** The root opens `card-list.php`. Every GIFTCARD row is displayed with its image, Card Name, and Points Required. The image and the name link to that card's details page. The page has a link to `card-add.php`.

**Actual result:** The root redirected to `card-list.php`, which showed "20 gift cards." All 20 cards displayed an image, a name, and points (for example, "Airport Lounge Pass Gift Card, Points required: 4,000"). All 20 images and all 20 names linked to `card-details.php?card_id=...`, and no image failed to load. The page has an **Add Gift Card** button, and the shared navigation shows **Gift Card List** and **Add Gift Card**.

**Status:** Pass

## T03: View Gift Card Details

**Steps and input:**
1. On the list, select the name **Bookstore Gift Card**.
2. Go back and select a card's image instead.

**Expected result:** `card-details.php?card_id=1` shows the card in a read-only format with its attributes and links to update, delete, and return to the list.

**Actual result:** The page opened at `card-details.php?card_id=1` and showed Card ID 1, Card Name Bookstore Gift Card, Card Type Retail, Card Value $25.00, and Points Required 2,500, with the card image. The values are plain text; the page has no input fields. It has three links: **Update Gift Card** (`card-update.php?card_id=1`), **Delete Gift Card** (`card-delete.php?card_id=1`), and **Return to Gift Card List** (`card-list.php`). Selecting a card's image also opened that card's details page.

**Status:** Pass

## T04: Add Gift Card

**Steps and input:**
1. On the list, select **Add Gift Card**.
2. Enter Card Name `Ski Resort Gift Card`, Card Type `Travel`, Card Value `80.50`, Points Required `8050`.
3. Select **Add Gift Card**.
4. Reload the list page.

**Expected result:** The form uses POST and has no Card ID field. The card is inserted, and the browser returns to `card-list.php`, where the new card appears.

**Actual result:** The form has four controls (Card Name, Card Type, Card Value, Points Required), no Card ID field, and `method="post"`. After submitting, the browser was redirected to `card-list.php`, which showed "The gift card was added," "21 gift cards," and the new card in the list. The database had the row `21 | Ski Resort Gift Card | Travel | 80.5 | 8050`. Reloading the list did not add a second copy, because the page redirects after the insert.

**Status:** Pass

## T05: Update Gift Card

**Steps and input:**
1. Open the details for **Ski Resort Gift Card** (Card ID 21) and select **Update Gift Card**.
2. Confirm the form shows the current values.
3. Change Card Name to `Ski Resort Weekend Gift Card`, Card Type to `Entertainment`, Card Value to `95`, and Points Required to `9500`.
4. Select **Save Changes**.
5. Send an update for the same card with a blank Card Name.

**Expected result:** The form is prefilled with the existing values. A valid update is saved and the updated card is displayed on `card-details.php`. An invalid update is not saved.

**Actual result:** `card-update.php?card_id=21` opened with Ski Resort Gift Card, Travel, 80.50, and 8050 already filled in. The Card ID is a hidden field, not an editable control. After saving, the browser was redirected to `card-details.php?card_id=21`, which showed "The gift card was updated" and the new values: Ski Resort Weekend Gift Card, Entertainment, $95.00, 9,500. The database row matched. The update with a blank Card Name returned HTTP 422, and the name in the database did not change.

**Status:** Pass

## T06: Delete Gift Card

**Steps and input:**
1. Open the details for Card ID 21 and select **Delete Gift Card**.
2. Check the row count, then select **Cancel**.
3. Select **Delete Gift Card** again, then **Yes, Delete Gift Card**.
4. Open `card-details.php?card_id=21`.
5. Send a POST to `card-delete.php` for Card ID 5 without the confirmation value.

**Expected result:** Opening the delete page does not delete anything. The page identifies the card and requires confirmation through a POST form. After confirmation, the card is deleted and the browser returns to `card-list.php`.

**Actual result:** `card-delete.php?card_id=21` showed "Delete "Ski Resort Weekend Gift Card" (Card ID 21)? This permanently removes the gift card and cannot be undone," with the card's image and all of its values. The confirmation form uses `method="post"`. The table still had 21 rows after the page was opened and after **Cancel**, which returned to the details page. After **Yes, Delete Gift Card**, the browser was redirected to `card-list.php`, which showed "The gift card was deleted" and "20 gift cards"; the card was no longer listed, and the database had no row with Card ID 21. Opening its details page afterward returned "Gift card not found" (HTTP 404). The POST without the confirmation value showed the confirmation page again, and Card ID 5 was not deleted.

**Status:** Pass

## T07: Invalid or altered input handling

These tests check that the server validates the data, even when the browser's own checks (`required`, the dropdown list, and the number limits) are skipped.

**Steps and input:**
1. On `card-add.php`, select **Add Gift Card** with every field empty.
2. Bypass the browser checks and submit: Card Name of three spaces, Card Type `Free Money` (not in the list), Card Value `-5`, Points Required `12.5`.
3. Send altered requests directly to `card-add.php`: Card Value `1e3`; a Card Name of 101 characters; no fields at all; Card Name sent as a list.
4. Add a card named `<script>alert(1)</script>`, and a card named `Robert'); DROP TABLE GIFTCARD;--`. View them, then delete both.
5. Open `card-list.php?msg=<script>alert(1)</script>`.

**Expected result:** Invalid data is rejected with helpful messages, nothing is saved, and the form keeps what was typed. Script and SQL text is treated as plain text and never runs.

**Actual result:**
1. The browser blocked the empty form. No request was sent, and the table still had 20 rows.
2. The server returned HTTP 422 and showed "The gift card was not saved" with four messages: "Card Name is required," "Choose a valid Card Type," "Card Value must be a number greater than 0, up to 10,000, with no more than 2 decimal places," and "Points Required must be a whole number greater than 0, up to 10,000,000." Each message also appeared under its field, and the form kept the entered values (-5 and 12.5). The table still had 20 rows.
3. All four altered requests returned HTTP 422 with no PHP warnings, and no row was added.
4. Both names passed validation, because they are text of a valid length, and were stored exactly as typed. The details page displayed `<script>alert(1)</script>` as visible text: no alert box appeared and no script element was added to the page, because output goes through `htmlspecialchars()`. The SQL text was saved as the card's name and the GIFTCARD table was not affected, because the insert uses a prepared statement. Both test cards were deleted afterward (20 rows).
5. The page loaded normally and displayed no message, because only the known message keys (`added`, `updated`, `deleted`) are shown.

**Status:** Pass

## T08: Invalid Gift Card identifier handling

**Steps and input:**
1. Open `card-details.php`, `card-update.php`, and `card-delete.php` with each of these `card_id` values: `abc`, `0`, `-1`, `1.5`, an empty value, `1 OR 1=1`, no `card_id` at all, and `card_id[]=1`.
2. Open the same three pages with `card_id=99999` (never existed) and `card_id=21` (deleted in T06).
3. Send a confirmed delete POST with `card_id=abc` and with `card_id=1 OR 1=1`.
4. Send an update POST with `card_id=99999`.

**Expected result:** Invalid identifiers are rejected before any query runs. Identifiers that are valid numbers but match no card show a "not found" page. No error details are shown, and no data changes.

**Actual result:**
1. All 24 requests (3 pages, 8 values) returned HTTP 400 with the page "Invalid gift card: The gift card link is not valid. Choose a gift card from the list," and a link back to the list.
2. All 6 requests returned HTTP 404 with "Gift card not found: That gift card does not exist. It may have been deleted."
3. Both POST requests returned HTTP 400, and the table still had 20 rows.
4. The update POST returned HTTP 404.

No response contained a PHP warning, an SQL error, or a file path.

**Status:** Pass

## Defect found and corrected

**Problem:** During testing on a phone-sized screen (390 pixels wide), the validation test page, `tests/validation-tests.php`, scrolled sideways. Its five-column results table was 748 pixels wide, so the whole page was wider than the screen. The four application pages did not have this problem.

**Fix:** The table is now wrapped in a `<div class="table-scroll">` with `overflow-x: auto`, and long text in the table cells can wrap (`overflow-wrap: anywhere` in `css/styles.css`).

**Retest result:** At 390 pixels wide, the page width is now 390 pixels with no sideways scrolling, and all 38 tests still display. Tests T01 through T08 were run again after the fix and all passed.

## Additional checks

- **Database file protection.** With the plain development server (`php -S 0.0.0.0:8000`), `/database/rewards.db` can be downloaded from the browser. With the included router (`php -S 0.0.0.0:8000 router.php`), requests for `/database/...`, `/includes/...`, `.sql` and `.db` files, and `/.git/...` returned 404, while every page, stylesheet, and image returned 200.
- **Missing database.** With `rewards.db` temporarily renamed, `card-list.php` returned HTTP 500 with "Something went wrong and the request could not be completed," showed no file paths, and did not create an empty database file.
- **Rebuilding the data.** Running `seed.sql` twice in a row left exactly 5 ACCOUNT rows and 20 GIFTCARD rows, because the script empties the tables before inserting.
- **Deleted IDs are not reused.** After Card ID 21 was deleted, the next card added received ID 22 (`AUTOINCREMENT`), so an old link can never open a different card.

-- AirAsia Rewards Redemption System: HW3 seed data
-- 5 ACCOUNT rows and 20 GIFTCARD rows. All data is made up for testing.
--
-- Run after schema.sql:
--     sqlite3 database/rewards.db < database/seed.sql
--
-- The script empties both tables and resets the ID counters first, so running
-- it again restores the original rows instead of adding duplicates.

DELETE FROM GIFTCARD;
DELETE FROM ACCOUNT;
DELETE FROM sqlite_sequence WHERE name IN ('GIFTCARD', 'ACCOUNT');

-- userId values match the USER rows that HW4 will add (users 1 to 5).
INSERT INTO ACCOUNT (userId, accountType, points) VALUES
    (1, 'Silver',   12000),
    (2, 'Gold',     45000),
    (3, 'Platinum', 120000),
    (4, 'Silver',   3500),
    (5, 'Gold',     800);

INSERT INTO GIFTCARD (cardName, cardType, cardValue, points) VALUES
    ('Bookstore Gift Card',          'Retail',          25.00,  2500),
    ('Department Store Gift Card',   'Retail',          50.00,  5000),
    ('Electronics Store Gift Card',  'Retail',          100.00, 10000),
    ('Home Improvement Gift Card',   'Retail',          75.00,  7500),
    ('Steakhouse Gift Card',         'Dining',          100.00, 10000),
    ('Pizza Restaurant Gift Card',   'Dining',          25.00,  2500),
    ('Sushi Restaurant Gift Card',   'Dining',          50.00,  5000),
    ('Family Diner Gift Card',       'Dining',          20.00,  2000),
    ('Coffee Shop Gift Card',        'Food & Beverage', 10.00,  1000),
    ('Bakery Gift Card',             'Food & Beverage', 15.00,  1500),
    ('Smoothie Bar Gift Card',       'Food & Beverage', 12.50,  1250),
    ('Movie Theater Gift Card',      'Entertainment',   30.00,  3000),
    ('Music Streaming Gift Card',    'Entertainment',   20.00,  2000),
    ('Video Game Store Gift Card',   'Entertainment',   60.00,  6000),
    ('Concert Tickets Gift Card',    'Entertainment',   150.00, 15000),
    ('Supermarket Gift Card',        'Grocery',         100.00, 10000),
    ('Farmers Market Gift Card',     'Grocery',         25.00,  2500),
    ('Hotel Stay Gift Card',         'Travel',          200.00, 20000),
    ('Airport Lounge Pass Gift Card','Travel',          40.00,  4000),
    ('Car Rental Gift Card',         'Travel',          125.00, 12500);

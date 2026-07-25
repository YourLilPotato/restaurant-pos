-- 03_verify_and_migrate.sql

USE restaurant_pos;

-- 1) Verify database objects exist
SHOW TABLES;

DESCRIBE order_types;
DESCRIBE payment_gateways;
DESCRIBE payment_statuses;
DESCRIBE menu_items;
DESCRIBE orders;
DESCRIBE order_items;
DESCRIBE payments;

-- 2) Verify foreign keys
SELECT
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'restaurant_pos'
  AND REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY TABLE_NAME, COLUMN_NAME;

-- 3) Verify seed data
SELECT * FROM order_types;
SELECT * FROM payment_gateways;
SELECT * FROM payment_statuses;

-- 4) If Task 1 order_items already existed and you need the Task 3 structure,
--    use ONE of the following approaches.

-- Option A: rebuild order_items (easy, but deletes existing order_items data)
-- DROP TABLE IF EXISTS order_items;
-- Then re-run the CREATE TABLE order_items statement from 01_schema.sql.

-- Option B: alter existing order_items table in place (safer if data matters)
-- Step 1: add nullable columns first
-- ALTER TABLE order_items
--     ADD COLUMN item_sold_name VARCHAR(120) NULL AFTER menu_item_id,
--     ADD COLUMN item_sold_price DECIMAL(10,2) NULL AFTER item_sold_name;

-- Step 2: backfill from current catalog for existing rows
-- UPDATE order_items oi
-- INNER JOIN menu_items mi ON mi.menu_item_id = oi.menu_item_id
-- SET oi.item_sold_name = mi.item_name,
--     oi.item_sold_price = mi.current_base_price
-- WHERE oi.item_sold_name IS NULL OR oi.item_sold_price IS NULL;

-- Step 3: enforce NOT NULL once data is backfilled
-- ALTER TABLE order_items
--     MODIFY COLUMN item_sold_name VARCHAR(120) NOT NULL,
--     MODIFY COLUMN item_sold_price DECIMAL(10,2) NOT NULL;

-- 5) Simple end-to-end test data
-- INSERT INTO menu_items (item_name, item_category, current_base_price)
-- VALUES ('Chicken Biryani', 'Main Course', 350.00);

-- INSERT INTO orders (order_type_id, order_number, subtotal_amount, tax_amount, delivery_fee_amount, total_amount)
-- VALUES (2, 'ORD-DEL-1001', 350.00, 17.50, 60.00, 427.50);

-- INSERT INTO order_items (order_id, menu_item_id, item_sold_name, item_sold_price, `quantity`, line_subtotal)
-- VALUES (LAST_INSERT_ID(), 1, 'Chicken Biryani', 350.00, 1, 350.00);

-- INSERT INTO payments (order_id, payment_gateway_id, payment_status_id, amount, gateway_transaction_ref, paid_at)
-- VALUES (1, 3, 2, 427.50, 'MBK-TRX-1001', NOW());

-- 01_schema.sql
-- Includes 3NF lookup tables and denormalized order_items for historical receipt auditing

CREATE DATABASE IF NOT EXISTS restaurant_pos
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE restaurant_pos;

CREATE TABLE IF NOT EXISTS order_types (
    order_type_id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type_code VARCHAR(20) NOT NULL,
    type_name VARCHAR(50) NOT NULL,
    UNIQUE KEY uq_order_types_code (type_code),
    UNIQUE KEY uq_order_types_name (type_name)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payment_gateways (
    payment_gateway_id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    gateway_code VARCHAR(20) NOT NULL,
    gateway_name VARCHAR(50) NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    UNIQUE KEY uq_payment_gateways_code (gateway_code),
    UNIQUE KEY uq_payment_gateways_name (gateway_name)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payment_statuses (
    payment_status_id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    status_code VARCHAR(20) NOT NULL,
    status_name VARCHAR(50) NOT NULL,
    UNIQUE KEY uq_payment_statuses_code (status_code),
    UNIQUE KEY uq_payment_statuses_name (status_name)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS menu_items (
    menu_item_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(120) NOT NULL,
    item_category VARCHAR(60) NULL,
    current_base_price DECIMAL(10,2) NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT chk_menu_items_price_nonnegative CHECK (current_base_price >= 0)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS orders (
    order_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_type_id TINYINT UNSIGNED NOT NULL,
    order_number VARCHAR(30) NOT NULL,
    ordered_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    subtotal_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    tax_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    service_charge_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    delivery_fee_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    notes VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_orders_order_number UNIQUE (order_number),
    CONSTRAINT fk_orders_order_type
        FOREIGN KEY (order_type_id)
        REFERENCES order_types(order_type_id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT,
    CONSTRAINT chk_orders_subtotal_nonnegative CHECK (subtotal_amount >= 0),
    CONSTRAINT chk_orders_tax_nonnegative CHECK (tax_amount >= 0),
    CONSTRAINT chk_orders_service_charge_nonnegative CHECK (service_charge_amount >= 0),
    CONSTRAINT chk_orders_delivery_fee_nonnegative CHECK (delivery_fee_amount >= 0),
    CONSTRAINT chk_orders_total_nonnegative CHECK (total_amount >= 0)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS order_items (
    order_item_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    menu_item_id BIGINT UNSIGNED NOT NULL,
    item_sold_name VARCHAR(120) NOT NULL,
    item_sold_price DECIMAL(10,2) NOT NULL,
    `quantity` INT UNSIGNED NOT NULL,
    line_subtotal DECIMAL(10,2) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_order_items_order
        FOREIGN KEY (order_id)
        REFERENCES orders(order_id)
        ON UPDATE RESTRICT
        ON DELETE CASCADE,
    CONSTRAINT fk_order_items_menu_item
        FOREIGN KEY (menu_item_id)
        REFERENCES menu_items(menu_item_id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT,
    CONSTRAINT chk_order_items_quantity_positive CHECK (`quantity` > 0),
    CONSTRAINT chk_order_items_price_nonnegative CHECK (item_sold_price >= 0),
    CONSTRAINT chk_order_items_line_subtotal_nonnegative CHECK (line_subtotal >= 0),
    KEY idx_order_items_order_id (order_id),
    KEY idx_order_items_menu_item_id (menu_item_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payments (
    payment_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    payment_gateway_id TINYINT UNSIGNED NOT NULL,
    payment_status_id TINYINT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    gateway_transaction_ref VARCHAR(100) NULL,
    paid_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_payments_order
        FOREIGN KEY (order_id)
        REFERENCES orders(order_id)
        ON UPDATE RESTRICT
        ON DELETE CASCADE,
    CONSTRAINT fk_payments_gateway
        FOREIGN KEY (payment_gateway_id)
        REFERENCES payment_gateways(payment_gateway_id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT,
    CONSTRAINT fk_payments_status
        FOREIGN KEY (payment_status_id)
        REFERENCES payment_statuses(payment_status_id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT,
    CONSTRAINT chk_payments_amount_nonnegative CHECK (amount >= 0),
    CONSTRAINT uq_payments_gateway_txn UNIQUE (payment_gateway_id, gateway_transaction_ref),
    KEY idx_payments_order_id (order_id),
    KEY idx_payments_paid_at (paid_at)
) ENGINE=InnoDB;

INSERT IGNORE INTO order_types (order_type_id, type_code, type_name) VALUES
(1, 'DINE_IN', 'Dine-in'),
(2, 'DELIVERY', 'Delivery'),
(3, 'TAKEAWAY', 'Takeaway');

INSERT IGNORE INTO payment_gateways (payment_gateway_id, gateway_code, gateway_name, is_active) VALUES
(1, 'CASH', 'Cash', TRUE),
(2, 'CARD', 'Card', TRUE),
(3, 'MOBILE_BANKING', 'Mobile Banking', TRUE);

INSERT IGNORE INTO payment_statuses (payment_status_id, status_code, status_name) VALUES
(1, 'PENDING', 'Pending'),
(2, 'SUCCESS', 'Success'),
(3, 'FAILED', 'Failed'),
(4, 'REFUNDED', 'Refunded');

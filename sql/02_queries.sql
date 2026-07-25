-- 02_queries.sql
-- Task 2 reconciliation query + a few useful support queries

USE restaurant_pos;

-- Daily Reconciliation Report:
-- All Delivery Orders successfully paid via Mobile Banking
SELECT
    o.order_id AS order_id,
    o.total_amount AS final_total,
    p.gateway_transaction_ref AS payment_reference
FROM orders o
INNER JOIN order_types ot
    ON ot.order_type_id = o.order_type_id
INNER JOIN payments p
    ON p.order_id = o.order_id
INNER JOIN payment_gateways pg
    ON pg.payment_gateway_id = p.payment_gateway_id
INNER JOIN payment_statuses ps
    ON ps.payment_status_id = p.payment_status_id
WHERE
    ot.type_code = 'DELIVERY'
    AND pg.gateway_code = 'MOBILE_BANKING'
    AND ps.status_code = 'SUCCESS'
ORDER BY o.ordered_at DESC;

-- Optional: full receipt-audit style join for one order
SELECT
    o.order_id,
    o.order_number,
    ot.type_name,
    oi.item_sold_name,
    oi.item_sold_price,
    oi.`quantity`,
    oi.line_subtotal,
    o.tax_amount,
    o.delivery_fee_amount,
    o.total_amount,
    pg.gateway_name,
    p.gateway_transaction_ref,
    ps.status_name
FROM orders o
INNER JOIN order_types ot ON ot.order_type_id = o.order_type_id
INNER JOIN order_items oi ON oi.order_id = o.order_id
INNER JOIN payments p ON p.order_id = o.order_id
INNER JOIN payment_gateways pg ON pg.payment_gateway_id = p.payment_gateway_id
INNER JOIN payment_statuses ps ON ps.payment_status_id = p.payment_status_id
WHERE o.order_id = 1;

-- Optional: daily total for successful mobile-banking delivery orders
SELECT
    DATE(o.ordered_at) AS order_date,
    COUNT(*) AS total_orders,
    SUM(o.total_amount) AS total_revenue
FROM orders o
INNER JOIN order_types ot ON ot.order_type_id = o.order_type_id
INNER JOIN payments p ON p.order_id = o.order_id
INNER JOIN payment_gateways pg ON pg.payment_gateway_id = p.payment_gateway_id
INNER JOIN payment_statuses ps ON ps.payment_status_id = p.payment_status_id
WHERE ot.type_code = 'DELIVERY'
  AND pg.gateway_code = 'MOBILE_BANKING'
  AND ps.status_code = 'SUCCESS'
GROUP BY DATE(o.ordered_at)
ORDER BY order_date DESC;

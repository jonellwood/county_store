-- see what will be changed ... dep_num is the key that binds

SELECT 
    od.order_details_id,
    od.price_id as old_price_id,
    od.product_id,
    od.size_id,
    od.item_price as old_item_price,
    p_active.price_id as new_price_id,
    p_active.price as new_price,
    p_active.vendor_id as new_vendor_id
FROM order_details od
INNER JOIN ord_approved oa ON od.order_details_id = oa.order_details_id
INNER JOIN prices p_old ON od.price_id = p_old.price_id
INNER JOIN prices p_active ON od.product_id = p_active.product_id 
    AND od.size_id = p_active.size_id 
    AND p_active.isActive = 1
WHERE od.emp_dept = 43111
    AND p_old.isActive = 0  -- Only get records with inactive prices
ORDER BY od.order_details_id DESC;

----------------------------------
-- actually do the change

UPDATE order_details od
INNER JOIN ord_approved oa ON od.order_details_id = oa.order_details_id
INNER JOIN prices p_old ON od.price_id = p_old.price_id
INNER JOIN prices p_active ON od.product_id = p_active.product_id 
    AND od.size_id = p_active.size_id 
    AND p_active.isActive = 1
SET 
    od.price_id = p_active.price_id,
    od.item_price = p_active.price,
    od.logo_fee = 5.00,
    od.tax = ((p_active.price + 5) * 0.09),
    od.line_item_total = (p_active.price + 5 + ((p_active.price + 5) * 0.09))
WHERE od.emp_dept = 43111
    AND p_old.isActive = 0;


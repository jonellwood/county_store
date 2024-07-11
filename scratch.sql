

SELECT r.*, c.first_name, c.last_name, c.email, c.emp_id, emp.deptNumber, emp.deptName 
FROM orders as r 
LEFT JOIN customers as c ON c.customer_id = r.customer_id 
JOIN (SELECT * from emp_ref) as emp
-- WHERE r.order_id=5
-- WHERE emp.deptNumber=41515
WHERE c.emp_id = 7143
GROUP BY r.order_id
;

------ this pulls the name, email, & department information from BIC instead of the user input - works as expected

SELECT r.*, c.emp_id, emp.deptNumber, emp.deptName, emp.email, emp.empName 
FROM orders as r 
LEFT JOIN customers as c ON c.customer_id = r.customer_id 
JOIN emp_ref as emp on c.emp_id=emp.empNumber 
;

----- This adds to the above by pulling in the order details as product name - works as expected

SELECT r.*, c.emp_id, emp.deptNumber, emp.deptName, emp.email, emp.empName, od.product_id, od.quantity, p.name 
FROM orders as r 
LEFT JOIN customers as c ON c.customer_id = r.customer_id 
JOIN emp_ref as emp on c.emp_id=emp.empNumber 
LEFT JOIN order_details as od on r.order_id = od.order_id
JOIN products as p on od.product_id = p.product_id
;

COLLATE utf8MB4_unicode_ci
orders == utf8_unicode_ci
customers == utf8_unicode_ci
order_details == utf8_unicode_ci
products == utf8_unicode_ci





CREATE VIEW `ord_ref` AS
SELECT r.*, c.emp_id, emp.deptNumber, emp.deptName, emp.email, emp.empName, od.product_id, od.quantity, p.name COLLATE utf8MB4_unicode_ci
FROM orders as r 
LEFT JOIN customers as c ON c.customer_id = r.customer_id 
JOIN emp_ref as emp on c.emp_id=emp.empNumber 
LEFT JOIN order_details as od on r.order_id = od.order_id
JOIN products as p on od.product_id = p.product_id
;

-- SHOW full columns from products;
-- SHOW full columns from orders;
-- SHOW full columns from customers; 
-- SHOW full columns from order_details;

SELECT * FROM uniform_orders.ord_ref
JOIN emp_ref on ord_ref.emp_id = emp_ref.empNumber
;

-- save from newindex trying to remove blocking 
 <!-- <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"
        async>
    </script> -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js"
        integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"
        async>
    </script> -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js"
        integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"
        async>
    </script> -->
    

SELECT
    p.product_id as OLDid,
    pn.product_id as NEWid,
    p.code,
    p.price,
    -- p.price_size_mod,
    (p.price + xs_inc) AS '1',
    (p.price + s_inc) AS '2',
    (p.price + m_inc) AS '3',
    (p.price + l_inc) AS '4',
    (p.price + xl_inc) AS '5',
    (p.price + xxl_inc) AS '6',
    (p.price + xxxl_inc) AS '7',
    (p.price + xxxxl_inc) AS '8',
    (p.price + xxxxxl_inc) AS '9',
    (p.price + xxxxxxl_inc) AS '10',
    (p.price + xxxxxxxl_inc) AS '11',
    (p.price + xxxxxxxxl_inc) AS '12',
    (p.price + xxxxxxxxxl_inc) AS '13',
    (p.price + xxxxxxxxxxl_inc) AS '14',
    (p.price + lt_inc) AS '15',
    (p.price + xlt_inc) AS '16',
    (p.price + xxlt_inc) AS '17',
    (p.price + xxxlt_inc) AS '18',
    (p.price + xxxxlt_inc) AS '19',
    (p.price + na_inc) AS '26'
FROM
    products p
JOIN
    price_mods pm ON pm.price_mod = p.price_size_mod
    join products_new pn on pn.product_id = p.product_id
WHERE
    vendor_id = 1
    AND isactive = true
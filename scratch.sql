

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
    
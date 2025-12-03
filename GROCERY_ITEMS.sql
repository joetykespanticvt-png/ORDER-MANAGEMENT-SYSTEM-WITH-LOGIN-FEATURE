
CREATE DATABASE IF NOT EXISTS grocery_db;
USE grocery_db;


CREATE TABLE IF NOT EXISTS categories (
    category_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE
);


CREATE TABLE IF NOT EXISTS grocery_items (
    item_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(255) NOT NULL UNIQUE,
    category_id INT(11) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    stock_quantity INT(11) NOT NULL,
    unit_of_measure ENUM('kg', 'g', 'L', 'ml', 'pcs', 'pack') NOT NULL,
    date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE RESTRICT
);


INSERT INTO categories (category_name) VALUES
('Produce'),
('Dairy'),
('Canned Goods'),
('Beverages'),
('Bakery');


INSERT INTO grocery_items (item_name, category_id, price, stock_quantity, unit_of_measure) VALUES
('Apples', (SELECT category_id FROM categories WHERE category_name = 'Produce'), 150.75, 50, 'kg'),
('Whole Milk', (SELECT category_id FROM categories WHERE category_name = 'Dairy'), 95.00, 30, 'L'),
('Tuna in Oil', (SELECT category_id FROM categories WHERE category_name = 'Canned Goods'), 45.20, 120, 'pcs'),
('Soda Can', (SELECT category_id FROM categories WHERE category_name = 'Beverages'), 30.00, 200, 'pcs'),
('Whole Wheat Bread', (SELECT category_id FROM categories WHERE category_name = 'Bakery'), 75.50, 40, 'pack'),
('Bananas', (SELECT category_id FROM categories WHERE category_name = 'Produce'), 65.00, 100, 'kg');


SELECT
    g.item_name,
    c.category_name,
    g.price,
    g.stock_quantity
FROM
    grocery_items g
INNER JOIN
    categories c ON g.category_id = c.category_id
WHERE
    g.stock_quantity < 50
ORDER BY
    g.price DESC;

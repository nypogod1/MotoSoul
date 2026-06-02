INSERT INTO products (brand, name, price, category, tags, image, hot, stock)
VALUES ('HJC', 'Шлем HJC RPHA 11', 38200, 'Шлемы', ARRAY['трек','лёгкий'], 'assets/products/thor.jpg', false, 10);


DELETE FROM products 
WHERE brand = 'HJC' 
  AND name = 'Шлем HJC RPHA 11' 
  AND price = 38200;
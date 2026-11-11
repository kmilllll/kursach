-- FLÔR 6: одноразове оновлення ТІЛЬКИ трьох старих демонстраційних подарунків.
-- Не видаляє таблиці, користувачів, замовлення чи чужі товари.
-- Резервну копію shop_db бажано зробити до виконання.
USE shop_db;
START TRANSACTION;
UPDATE products SET name='Квіткова коробка «Весняна ніжність»',
 details='Рожеві тюльпани у ніжній подарунковій композиції.',
 price=2490.00, image='gift_hatbox_blush.jpg', stock=8
 WHERE product_type='gift' AND name='Подарункова листівка';
UPDATE products SET name='Квітковий кошик «Гармонія»',
 details='Плетений кошик з рожевими півоніями у світлих природних відтінках.',
 price=2890.00, image='gift_basket_rose.jpg', stock=6
 WHERE product_type='gift' AND name='Преміум пакування';
UPDATE products SET name='Квітковий бокс «Теплі почуття»',
 details='Квіткова композиція з трояндами у святковому боксі.',
 price=2650.00, image='gift_box_pastel.jpg', stock=7
 WHERE product_type='gift' AND name='Подарунковий набір';
COMMIT;

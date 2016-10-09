--------------------
msUserBonus
--------------------
Author: marat Marabar <marat@marabar.ru>
--------------------

A basic Extra for MODx Revolution + miniShop2.

Feel free to suggest ideas/improvements/bugs on GitHub:
http://github.com/Marabar/msUserBonus/issues

--------------------

Внимание! Компонент работает только с авторизованными пользователями.

После установки в Свойствах товара появятся два поля: Цена закупочная
и не редактируемое поле: Прибыль

Добавить дополнительную колонку bonus_cost в таблице ms2_orders, выполнив запрос
phpMyAdmin:

ALTER TABLE `modx_ms2_orders` ADD `bonus_cost` DECIMAL(12,2) NULL DEFAULT '0.00' AFTER `cart_cost`;

Для оплаты товаров бонусами, можно воспользоваться компонентом msProfile.

Сниппеты
[[!msUserBonus]] - Показывает размер бонуса. Вызывать не кешированным.

В чанке письма доступны:
[[+bonus_cost]] - Размер записанной скидки по данному заказу


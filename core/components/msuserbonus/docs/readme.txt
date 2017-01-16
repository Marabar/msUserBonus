--------------------
msUserBonus
--------------------
Author: Marat Marabar <marat@marabar.ru>
--------------------

A basic Extra for MODx Revolution + miniShop2.

Feel free to suggest ideas/improvements/bugs on GitHub:
http://github.com/Marabar/msUserBonus/issues

--------------------------------------------------------------------------------

Компонент msUserProfile реализует возможность предоставления накопительного бонуса клиентам
магазина miniShop2 при оплате купленного товара. Также, даёт возможность оплатить
часть заказа накопленным бонусом. Для оплаты заказа полностью бонусом - воспользуйтесь
компонентом msProfile.

Принцип работы компонента такой:
Клиент кладёт товар в корзину, msUserProfile по формуле (формулы ниже) высчитывает размер бонуса
и выводит на экран, если есть что выводить. При оформлении заказа полученный бонус пишется
в bonus_cost таблицы ms2_orders. После оплаты заказа, этот бонус падает клиенту на счёт, с которого
он сможет, в дальнейшем, оплатить часть последующих покупок.
При частичной оплате товара бонусом, итоговая сумма к оплате снижается на размер этого бонуса
а сама частичная оплата пишется в bonus_payment таблицы ms2_orders и списывается со счёта клиента.
Если заказ, который был частично оплачен бонусом, переходит в статус Отменён, то бонус возвращается
на счёт клиента.

Внимание! Компонент работает только с авторизованными пользователями. (Закоментировано, заказчик отменил)

После установки в Свойствах товара появятся два поля: Цена закупочная
и не редактируемое поле: Прибыль.
В таблице заказов, предусмотрены дополнительные колонки: "Частично оплачено бонусом" и "Себестоимость",
также, в окне редактирования заказа, предусмотрено не редактируемое поле "Себестоимость".

Чтобы дополнительные колонки появились нужно их добавить в системную настройку miniShop2:
ms2_order_grid_fields - bonus_purchase,bonus_payment

Добавить дополнительные три колонки bonus_cost, bonus_payment и bonus_purchase в таблицу ms2_orders,
выполнив запрос phpMyAdmin:

ALTER TABLE `modx_ms2_orders` ADD `bonus_cost` DECIMAL(12,2) NULL DEFAULT '0.00' ,
ADD `bonus_payment` DECIMAL(12,2) NULL DEFAULT '0.00' ,
ADD `bonus_purchase` DECIMAL(12,2) NULL DEFAULT '0.00'
AFTER `cart_cost`;

-----------------

Добавить доп. колонку purchase_price в таблицу ms2_order_products,
выполнив запрос phpMyAdmin:

ALTER TABLE `modx_ms2_order_products`
ADD `purchase_price` DECIMAL(12,2) NULL DEFAULT '0.00' AFTER `price`;

Чтобы колонка "себестоимость" появилась в заказе списка товаров, нужно в системной
настройке ms2_order_product_fields добавить: purchase_price

-----------------

Прибыль высчитывается по формуле:
Прибыль = Цена - Цена закупочная

Бонусы:
(Цена - Цена закупочная) * количество(вес) - 1000

Для оплаты товаров бонусами, можно воспользоваться компонентом msProfile.

Сниппеты
[[!msUserBonus]] - Показывает размер бонуса. Вызывать не кешированным.
[[!msCopyBonus]] - Выводит поле с чекбоксом, размещать в чанке tpl.msOrder, пример
<div id="msBonus-row">
    [[!msCopyBonus]]
</div>

Плейсхолдеры msCopyBonus:
[[+msbonus]] - Размер разрешённой оплаты бонусом

Системные настройки msCopyBonus:
msuserbonus_err_bonus - текст ошибки, если пользователь попытается оплатить заказ полностью
                        бонусом. Как правило, это предложение воспользоваться оплатой через msProfile.
msuserbonus_err_sum_bonus - текст ошибки, если пользователь ввёл сумму бонусов больше, чем есть на его счету.
msuserbonus_number - Сумма, которая спишется с прибыли, всё что останется после списания зачисляется в бонус.
msuserbonus_size_bonus - Максимальный процент для оплаты бонусом.

В чанке письма доступны:
[[+bonus_cost]] - Размер записанной скидки по данному заказу
[[+bonus_payment]] - Размер частичной оплаты заказа бонусом

--------------------------------------------------------------------------------

Компонент писался для конкретного сайта, по всем вопросам пишите на marat@marabar.ru


--------------------------------------------------------------------------------
ВНИМАНИЕ!
Для возможности изменения суммы себестоимости товара необходимо добавить системное событие
сделав запрос в phpMyAdmin
INSERT INTO `product`.`modx_system_eventnames` (`name`, `service`, `groupname`) VALUES ('msOnProductUpdateOrder', '6', 'miniShop2');
INSERT INTO `product`.`modx_system_eventnames` (`name`, `service`, `groupname`) VALUES ('msOnProductRemoveOrder', '6', 'miniShop2');
INSERT INTO `product`.`modx_system_eventnames` (`name`, `service`, `groupname`) VALUES ('msOnProductCreateOrder', '6', 'miniShop2');

Следующие действия необходимо проделать, если не будет принят мой PR
https://github.com/bezumkin/miniShop2/pull/260
при установке компонента и после каждого обновления miniShop2.

В /core/components/miniShop2/processors/mgr/orders/product/update.class.php
добавить строчку:

public $afterSaveEvent = 'msOnProductUpdateOrder';

Должно получится так:

class msOrderProductUpdateProcessor extends modObjectUpdateProcessor
{
    public $classKey = 'msOrderProduct';
    public $languageTopics = array('minishop2:default');
    public $permission = 'msorder_save';
    public $afterSaveEvent = 'msOnProductUpdateOrder';
    ...
    ...
    ...
}

В /core/components/miniShop2/processors/mgr/orders/product/remove.class.php
добавить строчку:

public $afterRemoveEvent = 'msOnProductRemoveOrder';

Должно получится так:

class msOrderProductRemoveProcessor extends modObjectRemoveProcessor
{
    public $classKey = 'msOrderProduct';
    public $languageTopics = array('minishop2:default');
    public $permission = 'msorder_save';
    public $afterRemoveEvent = 'msOnProductRemoveOrder';
    ...
    ...
    ...
}

В /core/components/miniShop2/processors/mgr/orders/product/create.class.php
добавить строчку:

public $afterSaveEvent = 'msOnProductCreateOrder';

Должно получится так:

class msOrderProductCreateProcessor extends modObjectCreateProcessor
{
    public $classKey = 'msOrderProduct';
    public $languageTopics = array('minishop2:default');
    public $permission = 'msorder_save';
    public $afterSaveEvent = 'msOnProductCreateOrder';
    ...
    ...
    ...
}

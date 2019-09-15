-- Support cyrillic symbols
set NAMES 'utf8';

--Remove carousel from the bottom
delete from oc_layout_module where layout_id = 1 and code like '%carousel%';
--Remove slideshow from the top
delete from oc_layout_module where layout_id = 1 and code like '%slideshow%';

--Disable english language. Enable Russian. Make sure l18n patch was copied already.
update oc_language set sort_order = 2 where code = "en-gb";
update oc_language set status = 0 where code = "en-gb";
insert into oc_language set name = "Russian", code = "ru-ru", locale = "ru_RU.UTF-8,ru_RU,russian", image = "ru.png", directory = "russian", sort_order = 1, status = 1;

--Set Russian as a First language
update oc_language set language_id = 2 where name = 'English';
update oc_language set language_id = 1 where name = 'Russian';

--Update language settings for store 0
update oc_setting set value = 'ru-ru' where `key`='config_language' and store_id = 0;
update oc_setting set value = 'ru-ru' where `key`='config_admin_language' and store_id = 0;
update oc_setting set value = 'RUB' where `key`='config_currency' and store_id = 0;

insert into oc_currency set title = "", code = "RUB", symbol_left = "", symbol_right = ".", decimal_place = 0, value = 1.00000000, status = 1, date_modified = NOW();
Manually change name of currency and symbol_right in admin panel. For some reasons russian text is not inserted through SSH.

--Insert information
mysql -u root -p765b91475e opencart_samopek < oc_information.sql
mysql -u root -p765b91475e opencart_samopek < oc_information_description.sql

mysql -u root -p765b91475e opencart_samopek < oc_customer.sql
mysql -u root -p765b91475e opencart_samopek < oc_customer_activity.sql
mysql -u root -p765b91475e opencart_samopek < oc_customer_affiliate.sql
mysql -u root -p765b91475e opencart_samopek < oc_customer_approval.sql
mysql -u root -p765b91475e opencart_samopek < oc_customer_group.sql
mysql -u root -p765b91475e opencart_samopek < oc_customer_group_description.sql
mysql -u root -p765b91475e opencart_samopek < oc_customer_history.sql
mysql -u root -p765b91475e opencart_samopek < oc_customer_ip.sql
mysql -u root -p765b91475e opencart_samopek < oc_customer_login.sql
mysql -u root -p765b91475e opencart_samopek < oc_customer_online.sql
mysql -u root -p765b91475e opencart_samopek < oc_customer_reward.sql
mysql -u root -p765b91475e opencart_samopek < oc_customer_search.sql
mysql -u root -p765b91475e opencart_samopek < oc_customer_transaction.sql
mysql -u root -p765b91475e opencart_samopek < oc_customer_wishlist.sql

mysql -u root -p765b91475e opencart_samopek < oc_order.sql
mysql -u root -p765b91475e opencart_samopek < oc_order_history.sql
mysql -u root -p765b91475e opencart_samopek < oc_order_option.sql
mysql -u root -p765b91475e opencart_samopek < oc_order_product.sql
mysql -u root -p765b91475e opencart_samopek < oc_order_recurring.sql
mysql -u root -p765b91475e opencart_samopek < oc_order_recurring_transaction.sql
mysql -u root -p765b91475e opencart_samopek < oc_order_shipment.sql
mysql -u root -p765b91475e opencart_samopek < oc_order_status.sql
mysql -u root -p765b91475e opencart_samopek < oc_order_total.sql
mysql -u root -p765b91475e opencart_samopek < oc_order_voucher.sql


--Include into OMsI

create table oc_ms_samopek_product (product_id int(11) NOT NULL, ms_id int(11) NOT NULL, ms_uuid varchar(64) NOT NULL, ms_version int(3) NOT NULL, PRIMARY KEY (product_id));
create table oc_ms_samopek_category (category_id int(11) NOT NULL, ms_group_uuid varchar(64) NOT NULL, ms_version int(3) NOT NULL);

create table oc_ms_samopek_option (option_id int(11) NOT NULL, ms_variant_uuid varchar(64) NOT NULL, PRIMARY KEY (option_id));
create table oc_ms_samopek_product_option (product_option_value_id int(11) NOT NULL, ms_product_variant_uuid varchar(64) NOT NULL, ms_prodcut_variant_code int(11) NOT NULL, PRIMARY KEY (product_option_value_id));

create table oc_ms_samopek_attributes (attribute_id int(11) NOT NULL, ms_attribute_uuid varchar(64) NOT NULL, PRIMARY KEY (attribute_id));

--Turn off vouchers, shipping estimation, gift cards - Extensions->checkout.
--Extensions->shipping: Turn on SelfShipping, change Geographic Zone to AllAreas.
--Extensions->shipping: Turn on FixedPriceShipping, change Geographic Zone to AllAreas, Tax->No tax, price = 200

--Extensions->payment. Activate Cash,
--Extensions->Modules -> New Arrivals - Activate(update settings). + select it in Template HOME

--Install CDEK integration http://cdek.opencart.ru/documentation/

--
--
-- Localize Stock options. System->localization->Stock.


truncate oc_option;
truncate oc_option_description;
truncate oc_option_value;
truncate oc_option_value_description;

truncate oc_ms_samopek_attributes;
truncate oc_attribute;
truncate oc_attribute_description;
truncate oc_attribute_group;
truncate oc_attribute_group_description;

-- ALTERNATIVE
--drop table oc_option;
--create table oc_option (option_id int(11) NOT NULL AUTO_INCREMENT, type varchar(32) NOT NULL, sort_order int(3) NOT NULL, PRIMARY KEY (option_id));
--drop table oc_option_description;
--create table oc_option_description (option_id int(11) NOT NULL, language_id int(11) NOT NULL, name varchar(128) NOT NULL, PRIMARY KEY (option_id, language_id)) DEFAULT CHARSET=utf8;
--drop table oc_option_value;
--create table oc_option_value (option_value_id int(11) NOT NULL AUTO_INCREMENT, option_id int(11) NOT NULL, image varchar(255) NOT NULL, sort_order int(3) NOT NULL, PRIMARY KEY (option_value_id)) DEFAULT CHARSET=utf8;
--drop table oc_option_value_description;
--create table oc_option_value_description (option_value_id int(11) NOT NULL, language_id int(11) NOT NULL, option_id int(11) NOT NULL, name varchar(128) NOT NULL, PRIMARY KEY (option_value_id)) DEFAULT CHARSET=utf8;

Размер изображения товаров в корзине:
Модули->темы->самопек->изменить:
 - Размер большого изображения товара 376x376
 - Размер изображений товаров в корзине 87x87
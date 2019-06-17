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

--Include into OMsI
create table oc_ms_samopek_category (category_id int, ms_group_uuid TEXT);
create table oc_ms_samopek_product (product_id int, ms_id int, ms_version int);

--Turn off vouchers, shipping estimation, gift cards - Extensions->checkout.
--Extensions->shipping: Turn on SelfShipping, change Geographic Zone to AllAreas.
--Extensions->shipping: Turn on FixedPriceShipping, change Geographic Zone to AllAreas, Tax->No tax, price = 200

--Extensions->payment. Activate Cash,
--Extensions->Modules -> New Arrivals - Activate(update settings). + select it in Template HOME

--Install CDEK integration http://cdek.opencart.ru/documentation/
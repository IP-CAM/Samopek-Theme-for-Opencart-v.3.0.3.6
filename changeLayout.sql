--Remove carousel from the bottom
delete from oc_layout_module where layout_id = 1 and code like '%carousel%';
--Remove slideshow from the top
delete from oc_layout_module where layout_id = 1 and code like '%slideshow%';

--Disable english language. Enable Russian. Make sure l18n patch was copied already.
update oc_language set sort_order = 2 where code = "en-gb";
update oc_language set status = 0 where code = "en-gb";
insert into oc_language set name = "Russian", code = "ru-ru", locale = "ru_RU.UTF-8,ru_RU,russian", image = "ru.png", directory = "russian", sort_order = 1, status = 1;

--Update language settings for store 0
update oc_setting set value = 'ru-ru' where `key`='config_language' and store_id = 0;
update oc_setting set value = 'ru-ru' where `key`='config_admin_language' and store_id = 0;
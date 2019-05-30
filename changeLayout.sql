--Remove carousel from the bottom
delete from oc_layout_module where layout_id = 1 and code like '%carousel%';
--Remove slideshow from the top
delete from oc_layout_module where layout_id = 1 and code like '%slideshow%';
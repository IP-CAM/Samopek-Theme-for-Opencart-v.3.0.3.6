<?php
class samopek_ModelExtensionShippingCdek extends ModelExtensionShippingCdek {
	function getCities()
	{
		return $this->db->query("SELECT cityName FROM `" . DB_PREFIX . "cdek_city` WHERE `center` = '1'")->rows;
	}

	function getCity($city)
	{
		return $this->db->query("SELECT * FROM `" . DB_PREFIX . "cdek_city` WHERE `cityName` LIKE '%" . $this->db->escape($city) . "%' AND `center` = '1'")->row;
	}
}

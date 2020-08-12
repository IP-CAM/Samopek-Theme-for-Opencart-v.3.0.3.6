<?php
class samopek_ModelDesignBanner extends ModelDesignBanner {
    public function addBanner($data) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "banner SET name = '" . $this->db->escape($data['name']) . "', status = '" . (int)$data['status'] . "'");

        $banner_id = $this->db->getLastId();

        if (isset($data['banner_image'])) {
            foreach ($data['banner_image'] as $language_id => $value) {
                foreach ($value as $banner_image) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "banner_image SET banner_id = '" . (int)$banner_id . "', language_id = '" . (int)$language_id . "', title = '" .  $this->db->escape($banner_image['title']) . "', link = '" .  $this->db->escape($banner_image['link']) . "', image = '" .  $this->db->escape($banner_image['image']) . "', sort_order = '" .  (int)$banner_image['sort_order'] . "', description = '" .  $this->db->escape($banner_image['description']) . "'");
                }
            }
        }

        return $banner_id;
    }

    public function editBanner($banner_id, $data) {
        $this->db->query("UPDATE " . DB_PREFIX . "banner SET name = '" . $this->db->escape($data['name']) . "', status = '" . (int)$data['status'] . "' WHERE banner_id = '" . (int)$banner_id . "'");

        $this->db->query("DELETE FROM " . DB_PREFIX . "banner_image WHERE banner_id = '" . (int)$banner_id . "'");

        if (isset($data['banner_image'])) {
            foreach ($data['banner_image'] as $language_id => $value) {
                foreach ($value as $banner_image) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "banner_image SET banner_id = '" . (int)$banner_id . "', language_id = '" . (int)$language_id . "', title = '" .  $this->db->escape($banner_image['title']) . "', link = '" .  $this->db->escape($banner_image['link']) . "', image = '" .  $this->db->escape($banner_image['image']) . "', sort_order = '" . (int)$banner_image['sort_order'] . "', description = '" .  $this->db->escape($banner_image['description']) . "'");
                }
            }
        }
    }

    public function getBannerImages($banner_id) {
        $banner_image_data = array();

        $banner_image_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "banner_image WHERE banner_id = '" . (int)$banner_id . "' ORDER BY sort_order ASC");

        foreach ($banner_image_query->rows as $banner_image) {
            $banner_image_data[$banner_image['language_id']][] = array(
                'title'      => $banner_image['title'],
                'link'       => $banner_image['link'],
                'description' => $banner_image['description'],
                'image'      => $banner_image['image'],
                'sort_order' => $banner_image['sort_order']
            );
        }

        return $banner_image_data;
    }

}

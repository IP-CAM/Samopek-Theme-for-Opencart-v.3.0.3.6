<?php
class samopek_ModelCatalogProduct extends ModelCatalogProduct {
    public function hasOptions($product_id) {
        $result = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option WHERE product_id = " . (int)$product_id);
        if ($result->num_rows != 0) {
            return true;
        }
        return false;
    }

    public function getPath($product_id) {
        $result = $this->db->query("SELECT path_id FROM " . DB_PREFIX . "category_path WHERE category_id IN (SELECT category_id FROM " . DB_PREFIX . "product_to_category WHERE product_id = " . (int)$product_id . ") ORDER BY level");
        ob_start();                    // start buffer capture
        var_dump( $result );           // dump the values
        $contents = ob_get_contents(); // put the buffer into a variable
        ob_end_clean();                // end capture
        error_log( $contents );        //
        if ($result->num_rows != 0) {
            $path = '';
            foreach ($result->rows as $row) {
                $path = $path . $row['path_id'] . '_';
            }
            $path = 'path=' . rtrim($path, '_');
            error_log('PRODUCT_ID: ' . $product_id . ' PATH: ' . $path);
            return $path;
        }
        return "";
    }
}
?>

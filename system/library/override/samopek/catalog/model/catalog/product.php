<?php
class samopek_ModelCatalogProduct extends ModelCatalogProduct {
    public function hasOptions($product_id) {
        $result = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option WHERE product_id = " . (int)$product_id);
        ob_start();                    // start buffer capture
        var_dump( $result );           // dump the values
        $contents = ob_get_contents(); // put the buffer into a variable
        ob_end_clean();                // end capture
        error_log( $contents );        //
        if ($result->num_rows != 0) {
            error_log( "MAKO" );
            return true;
        }
        return false;
    }
}
?>

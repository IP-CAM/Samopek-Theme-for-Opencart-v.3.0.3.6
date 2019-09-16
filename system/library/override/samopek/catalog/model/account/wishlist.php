<?php
class samopek_ModelAccountWishlist extends ModelAccountWishlist {
    public function getWishlistProductsList() {
        $query = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "customer_wishlist WHERE customer_id = '" . (int)$this->customer->getId() . "'");
        $result = array();

        foreach ($query->rows as $row) {
            $result[] = $row['product_id'];
            error_log("A " . $row['product_id']);
        }
        return $result;
    }
}
?>

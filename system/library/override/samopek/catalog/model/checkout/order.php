<?php
class Samopek_ModelCheckoutOrder extends ModelCheckoutOrder {
    public function addOrder($data) {
        if (!isset($data['comment'])) {
            $data['comment'] = "";
        }
        parent::addOrder($data);
    }
}
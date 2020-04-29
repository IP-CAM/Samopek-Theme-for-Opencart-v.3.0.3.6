<?php
class samopek_Currency extends Currency {

    /* override method */
    public function format($number, $currency, $value = '', $format = true) {
        $symbol_left = $this->currencies[$currency]['symbol_left'];
        $symbol_right = $this->currencies[$currency]['symbol_right'];
        $decimal_place = $this->currencies[$currency]['decimal_place'];

        if (!$value) {
            $value = $this->currencies[$currency]['value'];
        }

        $amount = $value ? (float)$number * $value : (float)$number;

        $amount = round($amount, (int)$decimal_place);

        if (!$format) {
            return $amount;
        }

        $string = '';

        if ($symbol_left) {
            $string .= $symbol_left;
        }

        /* MAKO change start */
        if (intval($amount) == $amount) {
            $decimal_place = 0;
        }
        /* MAKO change end */

        $string .= number_format($amount, (int)$decimal_place, $this->language->get('decimal_point'), $this->language->get('thousand_point'));

        if ($symbol_right) {
            $string .= $symbol_right;
        }

        return $string;
    }
}
?>
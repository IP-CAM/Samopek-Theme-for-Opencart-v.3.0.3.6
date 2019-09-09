<?php
class samopek_ControllerCommonHeader extends ControllerCommonHeader {
    public function preRender( $template_buffer, $template_name, &$data ) {
        if ($template_name != $this->config->get( 'config_theme' ).'/template/common/header.twig') {
            return parent::preRender( $template_buffer, $template_name, $data );
        }
        $data['delivery'] = $this->url->link('information/information&information_id=6');
        $data['home_page'] = false;
        if ($this->request->server['REQUEST_METHOD'] == 'GET') {
            //error_log($this->request->get['route']);
            if (!isset($this->request->get['route']) || strpos($this->request->get['route'], "home") !== false) {
                $data['home_page'] = true;
            }
        }

        // Totals
        $this->load->model('setting/extension');

        $totals = array();
        $taxes = $this->cart->getTaxes();
        $total = 0;

        // Because __call can not keep var references so we put them into an array.
        $total_data = array(
            'totals' => &$totals,
            'taxes'  => &$taxes,
            'total'  => &$total
        );


        // Display prices
        if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
            error_log("ACASCSACAAAAAAAAAAAAAAAAAAAAAAAAAA");
            $sort_order = array();

            $results = $this->model_setting_extension->getExtensions('total');

            foreach ($results as $key => $value) {
                $sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
            }

            array_multisort($sort_order, SORT_ASC, $results);

            foreach ($results as $result) {
                if ($this->config->get('total_' . $result['code'] . '_status')) {
                    error_log("BBBBB" . $result['code']);
                    $this->load->model('extension/total/' . $result['code']);

                    // We have to put the totals in an array so that they pass by reference.
                    $this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
                }
            }

            $sort_order = array();

            foreach ($totals as $key => $value) {
                error_log("CCCC" . $key . $value['value']);
                $sort_order[$key] = $value['sort_order'];
            }

            error_log("GGG" . $total);

            array_multisort($sort_order, SORT_ASC, $totals);
        }

        $totalFormatted = sprintf($this->language->get('text_items'), $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0), $this->currency->format($total, $this->session->data['currency']));
        error_log("EEE" . $totalFormatted);

        $totalFormatted1 = sprintf($this->language->get('text_shopping_cart'), $totalFormatted);
        error_log("DDD" . $totalFormatted1);
        // Shopping cart
        $data['text_shopping_cart1'] = $totalFormatted;

        error_log("assad");
        error_log($data['home_page']);
        return parent::preRender($template_buffer, $template_name, $data);
    }
}
?>
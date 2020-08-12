<?php
class samopek_ControllerCheckoutCart extends ControllerCheckoutCart {
    public function preRender($template_buffer, $template_name, &$data)
    {
        if ($template_name != $this->config->get('config_theme') . '/template/checkout/cart.twig') {
            return parent::preRender($template_buffer, $template_name, $data);
        }
        $data['menu'] = $this->load->controller('common/menu');
        $data['modules'] = null;

        $this->load->language('checkout/cart');

        $this->document->setTitle($this->language->get('heading_title'));
        return parent::preRender($template_buffer, $template_name, $data);
    }
}
?>
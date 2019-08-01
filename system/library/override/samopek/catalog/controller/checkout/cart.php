<?php
class samopek_ControllerCheckoutCart extends ControllerCheckoutCart {
    public function preRender($template_buffer, $template_name, &$data)
    {
        if ($template_name != $this->config->get('config_theme') . '/template/checkout/cart.twig') {
            return parent::preRender($template_buffer, $template_name, $data);
        }
        $data['menu'] = $this->load->controller('common/menu');
        return parent::preRender($template_buffer, $template_name, $data);
    }
}
?>
<?php
class Samopek_ControllerCheckoutLogin extends ControllerCheckoutLogin {
    public function preRender($template_buffer, $template_name, &$data)
    {
        // Captcha
        if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('return', (array)$this->config->get('config_captcha_page'))) {
            $data['captcha'] = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha'), $this->error);
        } else {
            $data['captcha'] = '';
        }
        return parent::preRender($template_buffer, $template_name, $data);
    }
}
?>
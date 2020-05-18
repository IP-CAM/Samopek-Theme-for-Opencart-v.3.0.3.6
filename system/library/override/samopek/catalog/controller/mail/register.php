<?php
class samopek_ControllerMailRegister extends ControllerMailRegister {

    public function index(&$route, &$args, &$output) {
        $this->load->language('mail/register');

        $data['text_welcome'] = sprintf($this->language->get('text_welcome'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
        $data['text_login'] = $this->language->get('text_login');
        $data['text_approval'] = $this->language->get('text_approval');
        $data['text_service'] = $this->language->get('text_service');
        $data['text_thanks'] = $this->language->get('text_thanks');

        $this->load->model('account/customer_group');

        if (isset($args[0]['customer_group_id'])) {
            $customer_group_id = $args[0]['customer_group_id'];
        } else {
            $customer_group_id = $this->config->get('config_customer_group_id');
        }

        $customer_group_info = $this->model_account_customer_group->getCustomerGroup($customer_group_id);

        if ($customer_group_info) {
            $data['approval'] = $customer_group_info['approval'];
        } else {
            $data['approval'] = '';
        }

        $data['login'] = $this->url->link('account/login', '', true);
        $data['store'] = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');

        // MAKO0216 START
        $this->load->model('account/customer');
        $customer_info = $this->model_account_customer->getCustomerByEmail($args[0]['email']);
        $data['password'] = $customer_info['custom_field'];
        // MAKO0216 END


        $mail = new Mail($this->config->get('config_mail_engine'));
        $mail->parameter = $this->config->get('config_mail_parameter');
        $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
        $mail->smtp_username = $this->config->get('config_mail_smtp_username');
        $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
        $mail->smtp_port = $this->config->get('config_mail_smtp_port');
        $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

        $mail->setTo($args[0]['email']);
        $mail->setFrom($this->config->get('config_email'));
        $mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
        $mail->setSubject(sprintf($this->language->get('text_subject'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8')));
        $mail->setText($this->load->view('mail/register', $data));
        $mail->send();
    }

    public function alert(&$route, &$args, &$output) {
        if (!isset($args[0]['lastname'])) {
            $args[0]['lastname'] = "";
        }
        if (!isset($args[0]['telephone'])) {
            $args[0]['telephone'] = "";
        }
        return parent::alert($route, $args, $output);
    }
}
?>
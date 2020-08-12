<?php
class samopek_ControllerMailOrder extends ControllerMailOrder {
// Admin Alert Mail
    public function alert(&$route, &$args) {
        if (isset($args[0])) {
            $order_id = $args[0];
        } else {
            $order_id = 0;
        }

        if (isset($args[1])) {
            $order_status_id = $args[1];
        } else {
            $order_status_id = 0;
        }

        if (isset($args[2])) {
            $comment = $args[2];
        } else {
            $comment = '';
        }

        if (isset($args[3])) {
            $notify = $args[3];
        } else {
            $notify = '';
        }

        $order_info = $this->model_checkout_order->getOrder($order_id);

        if ($order_info && !$order_info['order_status_id'] && $order_status_id && in_array('order', (array)$this->config->get('config_mail_alert'))) {
            $this->load->language('mail/order_alert');

            // HTML Mail
            $data['text_received'] = $this->language->get('text_received');
            $data['text_order_id'] = $this->language->get('text_order_id');
            $data['text_date_added'] = $this->language->get('text_date_added');
            $data['text_order_status'] = $this->language->get('text_order_status');
            $data['text_product'] = $this->language->get('text_product');
            $data['text_total'] = $this->language->get('text_total');
            $data['text_comment'] = $this->language->get('text_comment');

            // MAKO - Add Customer info
            $data['customer_name'] = $order_info['firstname'] . " " . $order_info['lastname'];
            $data['customer_phone'] = $order_info['telephone'];
            $data['customer_email'] = $order_info['email'];

            // MAKO - Add Order details
            $data['order_payment_method'] = $order_info['payment_method'];
            $data['order_shipping_method'] = $order_info['shipping_method'];
            $data['order_shipping_address'] = $order_info['shipping_city'] . " "
                . $order_info['shipping_address_1'];


            $data['order_id'] = $order_info['order_id'];
            $data['date_added'] = date($this->language->get('date_format_short'), strtotime($order_info['date_added']));

            $order_status_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int)$order_status_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

            if ($order_status_query->num_rows) {
                $data['order_status'] = $order_status_query->row['name'];
            } else {
                $data['order_status'] = '';
            }

            $this->load->model('tool/upload');

            $data['products'] = array();

            $order_products = $this->model_checkout_order->getOrderProducts($order_id);

            foreach ($order_products as $order_product) {
                $option_data = array();

                $order_options = $this->model_checkout_order->getOrderOptions($order_info['order_id'], $order_product['order_product_id']);

                foreach ($order_options as $order_option) {
                    if ($order_option['type'] != 'file') {
                        $value = $order_option['value'];
                    } else {
                        $upload_info = $this->model_tool_upload->getUploadByCode($order_option['value']);

                        if ($upload_info) {
                            $value = $upload_info['name'];
                        } else {
                            $value = '';
                        }
                    }

                    $option_data[] = array(
                        'name'  => $order_option['name'],
                        'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
                    );
                }

                $data['products'][] = array(
                    'name'     => $order_product['name'],
                    'model'    => $order_product['model'],
                    'quantity' => $order_product['quantity'],
                    'option'   => $option_data,
                    'total'    => html_entity_decode($this->currency->format($order_product['total'] + ($this->config->get('config_tax') ? ($order_product['tax'] * $order_product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']), ENT_NOQUOTES, 'UTF-8')
                );
            }

            $data['vouchers'] = array();

            $order_vouchers = $this->model_checkout_order->getOrderVouchers($order_id);

            foreach ($order_vouchers as $order_voucher) {
                $data['vouchers'][] = array(
                    'description' => $order_voucher['description'],
                    'amount'      => html_entity_decode($this->currency->format($order_voucher['amount'], $order_info['currency_code'], $order_info['currency_value']), ENT_NOQUOTES, 'UTF-8')
                );
            }

            $data['totals'] = array();

            $order_totals = $this->model_checkout_order->getOrderTotals($order_id);

            foreach ($order_totals as $order_total) {
                $data['totals'][] = array(
                    'title' => $order_total['title'],
                    'value' => html_entity_decode($this->currency->format($order_total['value'], $order_info['currency_code'], $order_info['currency_value']), ENT_NOQUOTES, 'UTF-8')
                );
            }

            $data['comment'] = strip_tags($order_info['comment']);

            $mail = new Mail($this->config->get('config_mail_engine'));
            $mail->parameter = $this->config->get('config_mail_parameter');
            $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
            $mail->smtp_username = $this->config->get('config_mail_smtp_username');
            $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
            $mail->smtp_port = $this->config->get('config_mail_smtp_port');
            $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

            $mail->setTo($this->config->get('config_email'));
            $mail->setFrom($this->config->get('config_email'));
            $mail->setSender(html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8'));
            $mail->setSubject(html_entity_decode(sprintf($this->language->get('text_subject'), $this->config->get('config_name'), $order_info['order_id']), ENT_QUOTES, 'UTF-8'));
            // MAKO - Want to send HTML, not text
            //$mail->setText($this->load->view('mail/order_alert', $data));
            $mail->setHtml($this->load->view('mail/order_alert', $data));
            $mail->send();
           // file_put_contents('/tmp/testAlert.html', $this->load->view('mail/order_alert', $data));

            // Send to additional alert emails
            $emails = explode(',', $this->config->get('config_mail_alert_email'));

            foreach ($emails as $email) {
                if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $mail->setTo($email);
                    $mail->send();
                }
            }
        }
    }
}
?>
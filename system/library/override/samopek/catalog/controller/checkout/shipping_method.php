<?php
class samopek_ControllerCheckoutShippingMethod extends ControllerCheckoutShippingMethod {
	public function index() {
		$this->load->language('checkout/checkout');

        // MAKO0216 !!!!!!!!!!!!! Not sure why it is checked. To check.
		//if (isset($this->session->data['shipping_address'])) {
			// Shipping Methods
			$method_data = array();

			$this->load->model('setting/extension');

			$results = $this->model_setting_extension->getExtensions('shipping');

        // MAKO0216
        if (!isset($this->session->data['shipping_address'])) {
            $this->session->data['shipping_address'] = array();
            $this->session->data['shipping_address']['country_id'] = 176;
            $this->session->data['shipping_address']['zone_id'] = 2766;
        }
        // MAKO0216

			foreach ($results as $result) {
				if ($this->config->get('shipping_' . $result['code'] . '_status')) {
					$this->load->model('extension/shipping/' . $result['code']);

					$quote = $this->{'model_extension_shipping_' . $result['code']}->getQuote($this->session->data['shipping_address']);

					if ($quote) {

					    if ($quote['quote'][$result['code']]['cost'] == 0) {
                            $quote['quote'][$result['code']]['text'] = $this->language->get('text_shipping_pickup_price');
                        }

						$method_data[$result['code']] = array(
							'title'      => $quote['title'],
							'quote'      => $quote['quote'],
							'sort_order' => $quote['sort_order'],
							'error'      => $quote['error']
						);
					}
				}
			}

			$sort_order = array();

			foreach ($method_data as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $method_data);

			$this->session->data['shipping_methods'] = $method_data;
		//}

		if (empty($this->session->data['shipping_methods'])) {
			$data['error_warning'] = sprintf($this->language->get('error_no_shipping'), $this->url->link('information/contact'));
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['shipping_methods'])) {
			$data['shipping_methods'] = $this->session->data['shipping_methods'];
		} else {
			$data['shipping_methods'] = array();
		}

		if (isset($this->session->data['shipping_method']['code'])) {
			$data['code'] = $this->session->data['shipping_method']['code'];
		} else {
			$data['code'] = '';
		}

		if (isset($this->session->data['comment'])) {
			$data['comment'] = $this->session->data['comment'];
		} else {
			$data['comment'] = '';
		}

        $this->load->model('account/customer');

        $customer_info = $this->model_account_customer->getCustomer($this->customer->getId());
        $data['telephone'] = $customer_info['telephone'];

		$this->response->setOutput($this->load->view('checkout/shipping_method', $data));
	}

	public function save() {
		$this->load->language('checkout/checkout');

		$json = array();

		// Validate if shipping is required. If not the customer should not have reached this page.
		if (!$this->cart->hasShipping()) {
			$json['redirect'] = $this->url->link('checkout/checkout', '', true);
		}

		// Validate if shipping address has been set.
		if (!isset($this->session->data['shipping_address'])) {
			$json['redirect'] = $this->url->link('checkout/checkout', '', true);
		}

		// Validate cart has products and has stock.
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$json['redirect'] = $this->url->link('checkout/cart');
		}

		// Validate minimum quantity requirements.
		$products = $this->cart->getProducts();

		foreach ($products as $product) {
			$product_total = 0;

			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}

			if ($product['minimum'] > $product_total) {
				$json['redirect'] = $this->url->link('checkout/cart');

				break;
			}
		}

		if (!isset($this->request->post['shipping_method'])) {
			$json['error']['warning'] = $this->language->get('error_shipping');
		} else {
			$shipping = explode('.', $this->request->post['shipping_method']);
            //echo var_export($this->session->data['shipping_methods'], true);
			if (!isset($shipping[0]) || !isset($shipping[1]) || !isset($this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]])) {
				$json['error']['warning'] = $this->language->get('error_shipping');
			}
		}

        if (!$json && (utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 32)) {
            $json['error']['warning'] = $this->language->get('error_telephone');
        }

        if (isset($this->request->post['shipping_method']) && $this->request->post['shipping_method'] == 'flat.flat') {
            if (!$json && (utf8_strlen(trim($this->request->post['firstname'])) < 1) || (utf8_strlen(trim($this->request->post['firstname'])) > 32)) {
                $json['error']['warning'] = $this->language->get('error_firstname');
            }

            if (!$json && (utf8_strlen(trim($this->request->post['street'])) < 1)) {
                $json['error']['warning'] = $this->language->get('error_street');
            }

            if (!$json && (utf8_strlen(trim($this->request->post['house'])) < 1)) {
                $json['error']['warning'] = $this->language->get('error_house');
            }

            if (!$json && (utf8_strlen(trim($this->request->post['flat'])) < 1)) {
                $json['error']['warning'] = $this->language->get('error_flat');
            }
        }

        if (!$json) {
			$this->session->data['shipping_method'] = $this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]];
            $totals = $this->getTotals();
            $json['shipping_price'] = $totals['shipping'];
            $json['total_price'] = $totals['total'];
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

    public function getTotals() {
        $totalsToReturn = array();

        $totals = array();
        $taxes = $this->cart->getTaxes();
        $total = 0;

        $total_data = array(
            'totals' => &$totals,
            'taxes'  => &$taxes,
            'total'  => &$total
        );

        $this->load->model('setting/extension');

        $sort_order = array();

        $results = $this->model_setting_extension->getExtensions('total');

        foreach ($results as $result) {
            if ($this->config->get('total_' . $result['code'] . '_status')) {
                $this->load->model('extension/total/' . $result['code']);

                // We have to put the totals in an array so that they pass by reference.
                $this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
            }
        }

        foreach ($total_data['totals'] as $total_data_elem) {
            if ($total_data_elem['code'] == 'sub_total') {
                $totalsToReturn['cart'] = $this->currency->format($total_data_elem['value'], $this->session->data['currency']);
            }
            if ($total_data_elem['code'] == 'shipping') {
                $totalsToReturn['shipping'] = $this->currency->format($total_data_elem['value'], $this->session->data['currency']);
            }
        }

        $totalsToReturn['total'] = $this->currency->format($total_data['total'], $this->session->data['currency']);

        return $totalsToReturn;
    }
}
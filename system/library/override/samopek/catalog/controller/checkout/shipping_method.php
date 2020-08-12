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
        if (!isset($this->session->data['shipping_address']['city'])) {
            $this->session->data['shipping_address'] = array();
	        $this->session->data['shipping_address']['country_id'] = 176;
	        $this->session->data['shipping_address']['zone_id'] = 2766;
	        $this->session->data['shipping_address']['city'] = 'Москва';
  		} else {
            $this->load->model('extension/shipping/cdek');
        	$city = $this->model_extension_shipping_cdek->getCity($this->session->data['shipping_address']['city']);
        	if (empty($city)) {
		        $this->session->data['shipping_address']['country_id'] = 176;
		        $this->session->data['shipping_address']['zone_id'] = 2766;
		        $this->session->data['shipping_address']['city'] = 'Москва';
        	}
  		}
        // MAKO0216

			foreach ($results as $result) {
				if ($this->config->get('shipping_' . $result['code'] . '_status')) {
					$this->load->model('extension/shipping/' . $result['code']);
					$this->load->language('extension/shipping/' . $result['code']);

					$quote = $this->{'model_extension_shipping_' . $result['code']}->getQuote($this->session->data['shipping_address']);
					if ($quote) {
					    if (isset($quote['quote'][$result['code']]) && $quote['quote'][$result['code']]['cost'] == 0) {
                            $quote['quote'][$result['code']]['text'] = $this->language->get('text_shipping_pickup_price');
                        }
						$quote['quote'][key($quote['quote'])]['desc'] = $this->language->get('text_description');
						$quote['quote'][key($quote['quote'])]['date'] = $this->language->get('text_date');
						$cities = [];
						if ($result['code'] == 'cdek') {
							$quote['quote'][key($quote['quote'])]['text'] = $this->language->get('text_price');
							$quote['quote'][key($quote['quote'])]['tariff'] = key($quote['quote']);
							$cities = $this->{'model_extension_shipping_' . $result['code']}->getCities();
							unset($quote['quote'][key($quote['quote'])]['description']);
						}
						$method_data[$result['code']] = array(
							'title'      => $quote['title'],
							'code'       => $result['code'],
							'quote'      => $quote['quote'],
							'cities'     => $cities,
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

        $this->load->model('account/customer');
        $customer_info = $this->model_account_customer->getCustomer($this->customer->getId());

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

		if (isset($this->session->data['shipping_address']['street'])) {
			$data['street'] = $this->session->data['shipping_address']['street'];
		} else {
			$data['street'] = '';
		}

		if (isset($this->session->data['shipping_address']['house'])) {
			$data['house'] = $this->session->data['shipping_address']['house'];
		} else {
			$data['house'] = '';
		}

		if (isset($this->session->data['shipping_address']['flat'])) {
			$data['flat'] = $this->session->data['shipping_address']['flat'];
		} else {
			$data['flat'] = '';
		}

		if (isset($this->session->data['shipping_address']['floor'])) {
			$data['floor'] = $this->session->data['shipping_address']['floor'];
		} else {
			$data['floor'] = '';
		}

		if (isset($this->session->data['shipping_address']['frontdoor'])) {
			$data['frontdoor'] = $this->session->data['shipping_address']['frontdoor'];
		} else {
			$data['frontdoor'] = '';
		}

		if (isset($this->session->data['shipping_address']['city'])) {
			$data['city'] = $this->session->data['shipping_address']['city'];
		} else {
			$data['city'] = '';
		}

		if (isset($this->session->data['shipping_address']['name'])) {
			$data['name'] = $this->session->data['shipping_address']['name'];
		} else {
       		$data['name'] = $customer_info['firstname'] . ' ' . $customer_info['lastname'];
		}

		if (isset($this->session->data['shipping_address']['telephone'])) {
			$data['telephone'] = $this->session->data['shipping_address']['telephone'];
		} else {
	        $data['telephone'] = $customer_info['telephone'];
		}

		if (isset($this->session->data['cdek']['pvzaddress']) && $this->session->data['cdek']['pvzaddress'] != '') {
			$data['cdek_pvz'] = $this->session->data['cdek']['pvzaddress'];
		}

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
			if (!isset($shipping[0]) || !isset($shipping[1]) || !isset($this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]])) {
				$json['error']['warning'] = $this->language->get('error_shipping');
			} else {
				if ($shipping[0] == 'cdek') {
					if (!isset($this->request->post['city']) || (utf8_strlen(trim($this->request->post['city'])) < 1)) {
		                $json['error']['city'] = $this->language->get('error_shipping_city');
		            }
		            if (isset($this->session->data['cdek']['pvzlist']) && isset($this->session->data['cdek']['pvz'])) {
		            	$codes = [];
		            	foreach ($this->session->data['cdek']['pvzlist'] as $key => $value) {
		            		$codes[] = $value['Code'];
		            	}
		            	if (!in_array($this->session->data['cdek']['pvz'], $codes)) {
	               			$json['error']['need_pvz'] = $this->language->get('error_pvz');
		            	}
		            } elseif (isset($this->session->data['cdek']['pvzlist']) && (!isset($this->session->data['cdek']['pvz']) || $this->session->data['cdek']['pvz'] == '')) {
               			$json['error']['need_pvz'] = $this->language->get('error_pvz');
		            }
				}
			}
		}

        if (isset($this->request->post['shipping_method']) && isset($this->request->post['sm'])) {
        	foreach ($this->request->post['sm'][$this->request->post['shipping_method']] as $key => $value) {
        		if ($key == 'name' && (utf8_strlen(trim($value)) < 1)) {
	                $json['error']['sm[' . $this->request->post['shipping_method'] . '][' . $key . ']'] = $this->language->get('error_firstname');
	            }

	            if ($key == 'street' && (utf8_strlen(trim($value)) < 1)) {
	                $json['error']['sm[' . $this->request->post['shipping_method'] . '][' . $key . ']'] = $this->language->get('error_street');
	            }

	            if ($key == 'telephone' && (utf8_strlen($value) < 3) || (utf8_strlen($value) > 32)) {
		            $json['error']['sm[' . $this->request->post['shipping_method'] . '][' . $key . ']'] = $this->language->get('error_telephone');
		        }
        	}
        }

        if (!$json) {
			$this->session->data['shipping_method'] = $this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]];
			if (isset($this->request->post['shipping_method']) && isset($this->request->post['sm'])) {
				$address = [];
	        	foreach ($this->request->post['sm'][$this->request->post['shipping_method']] as $key => $value) {
	        		if ($key == 'comment') {
		        		$this->session->data[$key] = $value;
	        		} elseif ($key == 'name') {
		        		$name = explode(' ', $value);
		        		$surname = (isset($name[1]) ? $name[1] . ' ' : ' ') . (isset($name[2]) ? $name[2] : '');
		        		$this->session->data['shipping_address']['firstname'] = $this->session->data['payment_address']['firstname'] = $name[0];
		        		$this->session->data['shipping_address']['lastname'] = $this->session->data['payment_address']['lastname'] = $surname;
	        		} elseif (in_array($key, ['street', 'flat', 'floor', 'frontdoor', 'house'])) {
		        		$address[$key] = $value;
	        		} else {
		        		$this->session->data['shipping_address'][$key] = $value;
		        		$this->session->data['payment_address'][$key] = $value;
	        		}
	        	}
	        	if (!empty($address)) {
	        		$address_text = isset($address['street']) && $address['street'] != '' ? $address['street'] . ', ' : '';
	        		$address_text .= isset($address['house']) && $address['house'] != '' ? 'д. ' . $address['house'] . ', ' : '';
	        		$address_text .= isset($address['flat']) && $address['flat'] != '' ? 'кв. ' . $address['flat'] . ', ' : '';
	        		$address_text .= isset($address['frontdoor']) && $address['frontdoor'] != '' ? 'подъезд ' . $address['frontdoor'] . ', ' : '';
	        		$address_text .= isset($address['floor']) && $address['floor'] != '' ? 'этаж ' . $address['floor'] : '';
	        		$this->session->data['shipping_address']['address_1'] = $address_text;
	        		$this->session->data['payment_address']['address_1'] = $address_text;
	        	}
	        }
	        $this->session->data['payment_address']['country_id'] = $this->session->data['shipping_address']['country_id'];
	        $this->session->data['payment_address']['zone_id'] = $this->session->data['shipping_address']['zone_id'];
	        $this->session->data['payment_address']['city'] = $this->session->data['shipping_address']['city'];
			$json['redirect'] = str_replace('&amp;', '&', $this->url->link('checkout/checkout', 'payment', true));
			$this->session->data['shipping_ready'] = true;
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

    public function getCdekPvz() {
    	$json['status'] = false;
    	if (isset($this->request->post['city'])) {
	        $this->session->data['shipping_address']['country_id'] = 176;
	        $this->session->data['shipping_address']['zone_id'] = 2766;
       		$this->session->data['shipping_address']['city'] = $this->request->post['city'];
            $this->load->model('extension/shipping/cdek');
        	$this->model_extension_shipping_cdek->getQuote($this->session->data['shipping_address']);
	    	$json['status'] = true;
    	}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
    }
}
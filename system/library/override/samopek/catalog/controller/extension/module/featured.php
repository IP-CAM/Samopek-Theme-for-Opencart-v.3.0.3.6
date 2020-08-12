<?php
class samopek_ControllerExtensionModuleFeatured extends ControllerExtensionModuleFeatured {
	public function index($setting) {
		$this->load->language('extension/module/featured');

		$this->load->model('catalog/product');

        $this->load->model('account/wishlist');

		$this->load->model('tool/image');

		$data['products'] = array();

		if (!$setting['limit']) {
			$setting['limit'] = 4;
		}

        $cartlistIds = [];

        $cartlist = $this->cart->getProducts();
        foreach ($cartlist as $one) {
            $cartlistIds[] = $one['product_id'];
        }

        $wishListProductsIds = $this->model_account_wishlist->getWishlistProductsList();

		if (!empty($setting['product'])) {
			$products = array_slice($setting['product'], 0, (int)$setting['limit']);

			foreach ($products as $product_id) {
				$product_info = $this->model_catalog_product->getProduct($product_id);

				$product_path = $this->model_catalog_product->getPath($product_id);

				if ($product_info) {
					if ($product_info['image']) {
						$image = $this->model_tool_image->resize($product_info['image'], $setting['width'], $setting['height']);
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
					}

					if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
						$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$price = false;
					}

					if ((float)$product_info['special']) {
						$special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$special = false;
					}

					if ($this->config->get('config_tax')) {
						$tax = $this->currency->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price'], $this->session->data['currency']);
					} else {
						$tax = false;
					}

					if ($this->config->get('config_review_status')) {
						$rating = $product_info['rating'];
					} else {
						$rating = false;
					}

					// mako0216
                    // Added quantity
                    if ((int)$product_info['quantity']) {
                        $quantity = $product_info['quantity'];
                    } else {
                        $quantity = 0;
                    }

                    if ($product_info['quantity'] <= 0) {
                        $stock = $product_info['stock_status'];
                    } elseif ($this->config->get('config_stock_display')) {
                        $stock = $product_info['quantity'];
                    } else {
                        $stock = $this->language->get('text_instock');
                    }

                    $hasOptions = $this->model_catalog_product->hasOptions($product_info['product_id']);

                    $attributes = [];
                    foreach ($this->model_catalog_product->getProductAttributes($product_info['product_id']) as $one) {
                        foreach ($one['attribute'] as $item) {
                            if (count($attributes) == 2) break;
                            $attributes[] = $item['text'];
                        }
                    }

					$data['products'][] = array(
						'product_id'  => $product_info['product_id'],
						'thumb'       => $image,
						'name'        => $product_info['name'],
						'description' => utf8_substr(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
						'price'       => $price,
						'special'     => $special,
						'tax'         => $tax,
						'rating'      => $rating,
						'href'        => $this->url->link('product/product', $product_path . '&product_id=' . $product_info['product_id']),
                        'quantity'    => $quantity,
                        'stock'       => $stock,
                        'attributes' => $attributes,
                        'in_wishlist' => in_array($product_info['product_id'], $wishListProductsIds),
                        'in_cart' => in_array($product_info['product_id'], $cartlistIds),
                        'hasOptions'  => $hasOptions
					);
				}
			}
		}

		if ($data['products']) {
			return $this->load->view('extension/module/featured', $data);
		}
	}
}
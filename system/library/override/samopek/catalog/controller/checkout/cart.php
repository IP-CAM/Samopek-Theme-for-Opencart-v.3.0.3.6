<?php
class samopek_ControllerCheckoutCart extends ControllerCheckoutCart {
    public function index() {
        $this->load->language('checkout/cart');

        $this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'href' => $this->url->link('common/home'),
            'text' => $this->language->get('text_home')
        );

        $data['breadcrumbs'][] = array(
            'href' => $this->url->link('checkout/cart'),
            'text' => $this->language->get('heading_title')
        );

        if ($this->cart->hasProducts() || !empty($this->session->data['vouchers'])) {
            if (!$this->cart->hasStock() && (!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning'))) {
                $data['error_warning'] = $this->language->get('error_stock');
            } elseif (isset($this->session->data['error'])) {
                $data['error_warning'] = $this->session->data['error'];

                unset($this->session->data['error']);
            } else {
                $data['error_warning'] = '';
            }

            if ($this->config->get('config_customer_price') && !$this->customer->isLogged()) {
                $data['attention'] = sprintf($this->language->get('text_login'), $this->url->link('account/login'), $this->url->link('account/register'));
            } else {
                $data['attention'] = '';
            }

            if (isset($this->session->data['success'])) {
                $data['success'] = $this->session->data['success'];

                unset($this->session->data['success']);
            } else {
                $data['success'] = '';
            }

            $data['action'] = $this->url->link('checkout/cart/edit', '', true);

            if ($this->config->get('config_cart_weight')) {
                $data['weight'] = $this->weight->format($this->cart->getWeight(), $this->config->get('config_weight_class_id'), $this->language->get('decimal_point'), $this->language->get('thousand_point'));
            } else {
                $data['weight'] = '';
            }

            $this->load->model('tool/image');
            $this->load->model('tool/upload');
            $this->load->model('catalog/product');

            $data['products'] = array();

            $products = $this->cart->getProducts();

            foreach ($products as $product) {
                $product_total = 0;

                foreach ($products as $product_2) {
                    if ($product_2['product_id'] == $product['product_id']) {
                        $product_total += $product_2['quantity'];
                    }
                }

                if ($product['minimum'] > $product_total) {
                    $data['error_warning'] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);
                }

                if ($product['image']) {
                    $image = $this->model_tool_image->resize($product['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_height'));
                } else {
                    $image = '';
                }

                $option_data = array();

                foreach ($product['option'] as $option) {
                    if ($option['type'] != 'file') {
                        $value = $option['value'];
                    } else {
                        $upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

                        if ($upload_info) {
                            $value = $upload_info['name'];
                        } else {
                            $value = '';
                        }
                    }

                    $option_data[] = array(
                        'name'  => $option['name'],
                        'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
                    );
                }

                // Display prices
                if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                    $unit_price = $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'));
                    
                    $price = $this->currency->format($unit_price, $this->session->data['currency']);
                    $total = $this->currency->format($unit_price * $product['quantity'], $this->session->data['currency']);
                } else {
                    $price = false;
                    $total = false;
                }

                $recurring = '';

                if ($product['recurring']) {
                    $frequencies = array(
                        'day'        => $this->language->get('text_day'),
                        'week'       => $this->language->get('text_week'),
                        'semi_month' => $this->language->get('text_semi_month'),
                        'month'      => $this->language->get('text_month'),
                        'year'       => $this->language->get('text_year')
                    );

                    if ($product['recurring']['trial']) {
                        $recurring = sprintf($this->language->get('text_trial_description'), $this->currency->format($this->tax->calculate($product['recurring']['trial_price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['trial_cycle'], $frequencies[$product['recurring']['trial_frequency']], $product['recurring']['trial_duration']) . ' ';
                    }

                    if ($product['recurring']['duration']) {
                        $recurring .= sprintf($this->language->get('text_payment_description'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
                    } else {
                        $recurring .= sprintf($this->language->get('text_payment_cancel'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
                    }
                }

                $attributes = [];
                foreach ($this->model_catalog_product->getProductAttributes($product['product_id']) as $one) {
                  foreach ($one['attribute'] as $item) {
                      if (count($attributes) == 2) break;
                      $attributes[] = $item['text'];
                  }
                }
                $product_info = $this->model_catalog_product->getProduct($product['product_id']);

                $data['products'][] = array(
                    'cart_id'   => $product['cart_id'],
                    'thumb'     => $image,
                    'name'      => $product['name'],
                    'model'     => $product['model'],
                    'option'    => $option_data,
                    'recurring' => $recurring,
                    'attributes' => $attributes,
                    'quantity'  => $product['quantity'],
                    'quantity_fact' => $product_info['quantity'],
                    'stock'     => $product['stock'] ? true : !(!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning')),
                    'reward'    => ($product['reward'] ? sprintf($this->language->get('text_points'), $product['reward']) : ''),
                    'price'     => $price,
                    'total'     => $total,
                    'href'      => $this->url->link('product/product', 'product_id=' . $product['product_id'])
                );
            }

            // Gift Voucher
            if (!empty($this->session->data['voucher'])) {
                $this->load->model('extension/total/voucher');
                $voucher = $this->model_extension_total_voucher->getVoucher($this->session->data['voucher']);
                $data['voucher'] = array(
                    'key'         => $voucher['voucher_id'],
                    'amount'      => $this->currency->format($voucher['amount'], $this->session->data['currency']),
                    'remove'      => $this->url->link('checkout/cart/remove-voucher')
                );
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
                $sort_order = array();

                $results = $this->model_setting_extension->getExtensions('total');

                foreach ($results as $key => $value) {
                    $sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
                }

                array_multisort($sort_order, SORT_ASC, $results);

                foreach ($results as $result) {
                    if ($this->config->get('total_' . $result['code'] . '_status')) {
                        $this->load->model('extension/total/' . $result['code']);
                        
                        // We have to put the totals in an array so that they pass by reference.
                        $this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
                    }
                }

                $sort_order = array();

                foreach ($totals as $key => $value) {
                    $sort_order[$key] = $value['sort_order'];
                }

                array_multisort($sort_order, SORT_ASC, $totals);
            }

            $data['totals'] = array();

            foreach ($totals as $total) {
                $data['totals'][] = array(
                    'title' => $total['title'],
                    'text'  => $this->currency->format($total['value'], $this->session->data['currency'])
                );
            }

            $data['continue'] = $this->url->link('common/home');

            $data['checkout'] = $this->url->link('checkout/checkout', '', true);
            $data['cart'] = $this->url->link('checkout/cart', '', true);

            $this->load->model('setting/extension');

            $data['modules'] = array();
            
            $files = glob(DIR_APPLICATION . '/controller/extension/total/*.php');

            if ($files) {
                foreach ($files as $file) {
                    $result = $this->load->controller('extension/total/' . basename($file, '.php'));
                    if ($result && strpos($file, 'voucher.php') !== false) {
                        $data['modules'][] = $result;
                    }
                }
            }

            $data['column_left'] = $this->load->controller('common/column_left');
            $data['column_right'] = $this->load->controller('common/column_right');
            $data['content_top'] = $this->load->controller('common/content_top');
            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');
            $data['menu'] = $this->load->controller('common/menu');

            $this->response->setOutput($this->load->view('checkout/cart', $data));
        } else {
            $data['text_error'] = $this->language->get('text_empty');
            
            $data['continue'] = $this->url->link('common/home');

            unset($this->session->data['success']);

            $data['column_left'] = $this->load->controller('common/column_left');
            $data['column_right'] = $this->load->controller('common/column_right');
            $data['content_top'] = $this->load->controller('common/content_top');
            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');

            $this->response->setOutput($this->load->view('error/not_found', $data));
        }
    }

    public function add() {
        $this->load->language('checkout/cart');

        $json = array();

        if (isset($this->request->post['product_id'])) {
            $product_id = (int)$this->request->post['product_id'];
        } else {
            $product_id = 0;
        }

        $this->load->model('catalog/product');

        $product_info = $this->model_catalog_product->getProduct($product_id);

        if ($product_info) {
            if (isset($this->request->post['quantity'])) {
                $quantity = (int)$this->request->post['quantity'];
            } else {
                $quantity = 1;
            }

            if (isset($this->request->post['option'])) {
                $option = array_filter($this->request->post['option']);
            } else {
                $option = array();
            }

            $product_options = $this->model_catalog_product->getProductOptions($this->request->post['product_id']);

            foreach ($product_options as $product_option) {
                if ($product_option['required'] && empty($option[$product_option['product_option_id']])) {
                    $json['error']['option'][$product_option['product_option_id']] = sprintf($this->language->get('error_required'), $product_option['name']);
                }
            }

            if (isset($this->request->post['recurring_id'])) {
                $recurring_id = $this->request->post['recurring_id'];
            } else {
                $recurring_id = 0;
            }

            $recurrings = $this->model_catalog_product->getProfiles($product_info['product_id']);

            if ($recurrings) {
                $recurring_ids = array();

                foreach ($recurrings as $recurring) {
                    $recurring_ids[] = $recurring['recurring_id'];
                }

                if (!in_array($recurring_id, $recurring_ids)) {
                    $json['error']['recurring'] = $this->language->get('error_recurring_required');
                }
            }

            $inCart = false;

            if (!$json) {
                $quantityInCart = 0;

                $cartlist = $this->cart->getProducts();
                foreach ($cartlist as $one) {
                    $cartlistIds[] = $one['product_id'];
                    if ($product_info['product_id'] == $one['product_id']) $quantityInCart += (int)$one['quantity'];
                    if ($product_info['product_id'] == $one['product_id']) $inCart = true;
                }
                if ($product_info['quantity'] >= ($quantityInCart + $quantity)) {
                    $this->cart->add($this->request->post['product_id'], $quantity, $option, $recurring_id);
                } else {
                    if (($product_info['quantity'] - $quantityInCart) > 0) {
                        $this->cart->add($this->request->post['product_id'], $product_info['quantity'] - $quantityInCart, $option, $recurring_id);
                    }
                    $json['redirect'] = $this->url->link('checkout/cart');
                }

                $json['success'] = sprintf($this->language->get('text_success'), $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']), $product_info['name'], $this->url->link('checkout/cart'));
                
                $this->load->language('product/added');
                $this->load->model('tool/image');

                $cartlistIds = [];

                $cartlist = $this->cart->getProducts();
                foreach ($cartlist as $one) {
                    $cartlistIds[] = $one['product_id'];
                }
                $this->load->model('account/wishlist');
                $wishListProductsIds = $this->model_account_wishlist->getWishlistProductsList();

                $attributes = [];
                foreach ($this->model_catalog_product->getProductAttributes($product_info['product_id']) as $one) {
                  foreach ($one['attribute'] as $item) {
                      if (count($attributes) == 2) break;
                      $attributes[] = $item['text'];
                  }
                }

                $data['product_info'] = [
                    'product_id' => $product_info['product_id'],
                    'name' => $product_info['name'],
                    'model' =>  $product_info['model'],
                    'quantity' => $product_info['quantity'],
                    'attributes' => $attributes,
                    'max' => $product_info['quantity'] - $quantityInCart,
                    'in_wishlist' => in_array($product_info['product_id'], $wishListProductsIds),
                    'in_cart' => in_array($product_info['product_id'], $cartlistIds),
                    'current_quantity' => 0,
                    'current_total' => 0,
                ];

                $cartProducts = $this->cart->getProducts();
                foreach ($cartProducts as $one) {
                    if ($one['product_id'] != $product_info['product_id']) continue;
                    $data['product_info']['cart_id'] = $one['cart_id'];
                    $unit_price = $this->tax->calculate($one['price'], $one['tax_class_id'], $this->config->get('config_tax'));
                    $data['product_info']['current_quantity'] = $one['quantity'];
                    $data['product_info']['current_total'] = $this->currency->format($unit_price * $one['quantity'], $this->session->data['currency']);
                }

                $data['product_info']['tags'] = array();

                if ($product_info['tag']) {
                    $tags = explode(',', $product_info['tag']);

                    foreach ($tags as $tag) {
                        $data['product_info']['tags'][] = array(
                            'tag'  => trim($tag),
                            'href' => $this->url->link('product/search', 'tag=' . trim($tag))
                        );
                    }
                }

                if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                    $data['product_info']['price'] = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                } else {
                    $data['product_info']['price'] = false;
                }

                if ((float)$product_info['special']) {
                    $data['product_info']['special'] = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                } else {
                    $data['product_info']['special'] = false;
                }

                if ($product_info['image']) {
                    $data['product_info']['image'] = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
                } else {
                    $data['product_info']['image'] = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
                }

                $data['products'] = array();

                $results = $this->model_catalog_product->getProductRelated($this->request->post['product_id']);

                foreach ($results as $result) {
                    if ($result['image']) {
                        $image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
                    } else {
                        $image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
                    }

                    if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                        $price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                    } else {
                        $price = false;
                    }

                    if ((float)$result['special']) {
                        $special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                    } else {
                        $special = false;
                    }

                    if ($this->config->get('config_tax')) {
                        $tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
                    } else {
                        $tax = false;
                    }

                    if ($this->config->get('config_review_status')) {
                        $rating = (int)$result['rating'];
                    } else {
                        $rating = false;
                    }

                    if ((int)$result['quantity']) {
                      $quantity = $result['quantity'];
                    } else {
                      $quantity = 0;
                    }

                    if ($result['quantity'] <= 0) {
                      $stock = $result['stock_status'];
                    } elseif ($this->config->get('config_stock_display')) {
                      $stock = $result['quantity'];
                    } else {
                      $stock = $this->language->get('text_instock');
                    }

                    $attributes = [];
                    foreach ($this->model_catalog_product->getProductAttributes($result['product_id']) as $one) {
                      foreach ($one['attribute'] as $item) {
                          if (count($attributes) == 2) break;
                          $attributes[] = $item['text'];
                      }
                    }

                    $data['products'][] = array(
                        'product_id'  => $result['product_id'],
                        'thumb'       => $image,
                        'name'        => $result['name'],
                        'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
                        'price'       => $price,
                        'special'     => $special,
                        'tax'         => $tax,
                        'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
                        'rating'      => $rating,
                        'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id']),
                        'quantity'    => $quantity,
                        'stock'       => $stock,
                        'attributes' => $attributes,
                        'in_wishlist' => in_array($result['product_id'], $wishListProductsIds),
                        'in_cart' => in_array($result['product_id'], $cartlistIds),
                    );
                }

                if (!$inCart) {
                    $data['cart'] = $this->url->link('checkout/cart');
                    $json['modal'] = $this->load->view('product/added', $data);
                }

                // Unset all shipping and payment methods
                unset($this->session->data['shipping_method']);
                unset($this->session->data['shipping_methods']);
                unset($this->session->data['payment_method']);
                unset($this->session->data['payment_methods']);

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
                    $sort_order = array();

                    $results = $this->model_setting_extension->getExtensions('total');

                    foreach ($results as $key => $value) {
                        $sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
                    }

                    array_multisort($sort_order, SORT_ASC, $results);

                    foreach ($results as $result) {
                        if ($this->config->get('total_' . $result['code'] . '_status')) {
                            $this->load->model('extension/total/' . $result['code']);

                            // We have to put the totals in an array so that they pass by reference.
                            $this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
                        }
                    }

                    $sort_order = array();

                    foreach ($totals as $key => $value) {
                        $sort_order[$key] = $value['sort_order'];
                    }

                    array_multisort($sort_order, SORT_ASC, $totals);
                }

                $json['total'] = sprintf($this->language->get('text_items'), $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0), $this->currency->format($total, $this->session->data['currency']));
            } else {
                $json['redirect'] = str_replace('&amp;', '&', $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']));
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function edit() {
        $this->load->language('checkout/cart');
        $this->load->model('catalog/product');

        $json = array();

        // Update
        if (!empty($this->request->post['quantity'])) {
            if (!isset($this->request->post['key'])) {
                foreach ($this->request->post['quantity'] as $key => $value) {
                    $quantityInCart = 0;

                    $cartlist = $this->cart->getProducts();
                    foreach ($cartlist as $one) {
                        if ($key == $one['cart_id']) $quantityInCart += (int)$one['quantity'];
                        if ($key == $one['cart_id']) $product_id = $one['product_id'];
                    }
                    $product_info = $this->model_catalog_product->getProduct($product_id);

                    if ($product_info['quantity'] >= $value) {
                        $this->cart->update($key, $value);
                    } else {
                        if (($product_info['quantity'] - $quantityInCart) > 0) {
                           $this->cart->update($key, $product_info['quantity'] - $quantityInCart);
                        }
                        $json['redirect'] = $this->url->link('checkout/cart');
                    }
                }   
            } else {
                $quantityInCart = 0;

                $cartlist = $this->cart->getProducts();
                foreach ($cartlist as $one) {
                    if ($this->request->post['key'] == $one['cart_id']) $quantityInCart += (int)$one['quantity'];
                    if ($this->request->post['key'] == $one['cart_id']) $product_id = $one['product_id'];
                }
                $product_info = $this->model_catalog_product->getProduct($product_id);

                if ($product_info['quantity'] >= $this->request->post['quantity']) {
                    $this->cart->update($this->request->post['key'], (int)$this->request->post['quantity']);
                } else {
                    if (($product_info['quantity'] - $quantityInCart) > 0) {
                       $this->cart->update($this->request->post['key'], $product_info['quantity'] - $quantityInCart);
                    }
                    $json['redirect'] = $this->url->link('checkout/cart');
                }
            }

            $this->session->data['success'] = $this->language->get('text_remove');

            unset($this->session->data['shipping_method']);
            unset($this->session->data['shipping_methods']);
            unset($this->session->data['payment_method']);
            unset($this->session->data['payment_methods']);
            unset($this->session->data['reward']);

            if (isset($this->request->post['redirect'])) {
                $json['redirect'] = $this->url->link('checkout/cart');
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($json));
            }

            if (isset($this->request->post['key'])) {
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
                    $sort_order = array();

                    $results = $this->model_setting_extension->getExtensions('total');

                    foreach ($results as $key => $value) {
                        $sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
                    }

                    array_multisort($sort_order, SORT_ASC, $results);

                    foreach ($results as $result) {
                        if ($this->config->get('total_' . $result['code'] . '_status')) {
                            $this->load->model('extension/total/' . $result['code']);

                            // We have to put the totals in an array so that they pass by reference.
                            $this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
                        }
                    }

                    $sort_order = array();

                    foreach ($totals as $key => $value) {
                        $sort_order[$key] = $value['sort_order'];
                    }

                    array_multisort($sort_order, SORT_ASC, $totals);
                }

                $json['total'] = sprintf($this->language->get('text_items'), $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0), $this->currency->format($total, $this->session->data['currency']));

                $cartProducts = $this->cart->getProducts();
                foreach ($cartProducts as $one) {
                    if ($one['cart_id'] != $this->request->post['key']) continue;
                    $unit_price = $this->tax->calculate($one['price'], $one['tax_class_id'], $this->config->get('config_tax'));
                    $json['current_total'] = $this->currency->format($unit_price * $one['quantity'], $this->session->data['currency']);
                }
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function remove() {
        $this->load->language('checkout/cart');

        $json = array();

        // Remove
        if (isset($this->request->post['key'])) {
            $this->cart->remove($this->request->post['key']);

            unset($this->session->data['vouchers'][$this->request->post['key']]);

            $json['success'] = $this->language->get('text_remove');

            unset($this->session->data['shipping_method']);
            unset($this->session->data['shipping_methods']);
            unset($this->session->data['payment_method']);
            unset($this->session->data['payment_methods']);
            unset($this->session->data['reward']);

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
                $sort_order = array();

                $results = $this->model_setting_extension->getExtensions('total');

                foreach ($results as $key => $value) {
                    $sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
                }

                array_multisort($sort_order, SORT_ASC, $results);

                foreach ($results as $result) {
                    if ($this->config->get('total_' . $result['code'] . '_status')) {
                        $this->load->model('extension/total/' . $result['code']);

                        // We have to put the totals in an array so that they pass by reference.
                        $this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
                    }
                }

                $sort_order = array();

                foreach ($totals as $key => $value) {
                    $sort_order[$key] = $value['sort_order'];
                }

                array_multisort($sort_order, SORT_ASC, $totals);
            }

            $json['total'] = $this->cart->countProducts() == 0 ? 0 : sprintf($this->language->get('text_items'), $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0), $this->currency->format($total, $this->session->data['currency']));
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function removeVoucher()
    {
        unset($this->session->data['voucher']);
        $this->response->redirect($this->url->link('checkout/cart'));
    }
}
?>
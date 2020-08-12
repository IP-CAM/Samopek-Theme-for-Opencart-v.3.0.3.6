<?php
class ControllerExtensionModuleBlockInsta extends Controller {
	private $error = array();

	private function getInstaImages() {
		for ($i=1; $i <= 9; $i++) { 
			if (isset($this->request->post['post_' . $i]) && $this->request->post['post_' . $i] != '') {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, 'https://api.instagram.com/oembed/?callback=&url=' . $this->request->post['post_' . $i]);
			    curl_setopt($ch, CURLOPT_HEADER, 0);
			    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			    $single_json_object = curl_exec($ch);
			    curl_close($ch);

				$single_decode = json_decode($single_json_object, true);
				$this->request->post['post_' . $i . '_image'] = $single_decode['thumbnail_url'];
			}
		}
	}

	public function index() {
		$this->load->language('extension/module/block_insta');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/module');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->getInstaImages();

			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('block_insta', $this->request->post);
			} else {
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			}

			$this->cache->delete('product');

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/block_insta', 'user_token=' . $this->session->data['user_token'], true)
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/block_insta', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
			);
		}

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/block_insta', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/block_insta', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($module_info)) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}

	    if (isset($this->request->post['title'])) {
	        $data['title'] = $this->request->post['title'];
	    } elseif (!empty($module_info)) {
	        $data['title'] = $module_info['title'];
	    } else {
	        $data['title'] = '';
	    }

	    if (isset($this->request->post['description'])) {
	        $data['description'] = $this->request->post['description'];
	    } elseif (!empty($module_info)) {
	        $data['description'] = $module_info['description'];
	    } else {
	        $data['description'] = '';
	    }

	    if (isset($this->request->post['link_text'])) {
	        $data['link_text'] = $this->request->post['link_text'];
	    } elseif (!empty($module_info)) {
	        $data['link_text'] = $module_info['link_text'];
	    } else {
	        $data['link_text'] = '';
	    }

	    if (isset($this->request->post['link'])) {
	        $data['link'] = $this->request->post['link'];
	    } elseif (!empty($module_info)) {
	        $data['link'] = $module_info['link'];
	    } else {
	        $data['link'] = '';
	    }

	    if (isset($this->request->post['vk_link'])) {
	        $data['vk_link'] = $this->request->post['vk_link'];
	    } elseif (!empty($module_info)) {
	        $data['vk_link'] = $module_info['vk_link'];
	    } else {
	        $data['vk_link'] = '';
	    }

	    if (isset($this->request->post['insta_link'])) {
	        $data['insta_link'] = $this->request->post['insta_link'];
	    } elseif (!empty($module_info)) {
	        $data['insta_link'] = $module_info['insta_link'];
	    } else {
	        $data['insta_link'] = '';
	    }

		for ($i = 1; $i <= 9; $i++) {
			if (isset($this->request->post['post_' . $i])) {
				$data['post_' . $i] = $this->request->post['post_' . $i];
			} elseif (!empty($module_info)) {
				$data['post_' . $i] = $module_info['post_' . $i];
			} else {
				$data['post_' . $i] = '';
			}
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($module_info)) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = '';
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/block_insta', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/block_insta')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		return !$this->error;
	}
}

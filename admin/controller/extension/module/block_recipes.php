<?php
class ControllerExtensionModuleBlockRecipes extends Controller {
    private $error = array();

    public function index() {
        $this->load->language('extension/module/block_recipes');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/module');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            if (!isset($this->request->get['module_id'])) {
                $this->model_setting_module->addModule('block_recipes', $this->request->post);
            } else {
                $this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
            }

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
                'href' => $this->url->link('extension/module/block_recipes', 'user_token=' . $this->session->data['user_token'], true)
            );
        } else {
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/module/block_recipes', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
            );
        }

        if (!isset($this->request->get['module_id'])) {
            $data['action'] = $this->url->link('extension/module/block_recipes', 'user_token=' . $this->session->data['user_token'], true);
        } else {
            $data['action'] = $this->url->link('extension/module/block_recipes', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
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

        if (isset($this->request->post['banner_id'])) {
            $data['banner_id'] = $this->request->post['banner_id'];
        } elseif (!empty($module_info)) {
            $data['banner_id'] = $module_info['banner_id'];
        } else {
            $data['banner_id'] = '';
        }

        $this->load->model('design/banner');

        $data['banners'] = $this->model_design_banner->getBanners();

        if (isset($this->request->post['color_title_1'])) {
            $data['color_title_1'] = $this->request->post['color_title_1'];
        } elseif (!empty($module_info)) {
            $data['color_title_1'] = $module_info['color_title_1'];
        } else {
            $data['color_title_1'] = '';
        }
        if (isset($this->request->post['color_title_2'])) {
            $data['color_title_2'] = $this->request->post['color_title_2'];
        } elseif (!empty($module_info)) {
            $data['color_title_2'] = $module_info['color_title_2'];
        } else {
            $data['color_title_2'] = '';
        }
        if (isset($this->request->post['color_title_3'])) {
            $data['color_title_3'] = $this->request->post['color_title_3'];
        } elseif (!empty($module_info)) {
            $data['color_title_3'] = $module_info['color_title_3'];
        } else {
            $data['color_title_3'] = '';
        }
        if (isset($this->request->post['color_title_4'])) {
            $data['color_title_4'] = $this->request->post['color_title_4'];
        } elseif (!empty($module_info)) {
            $data['color_title_4'] = $module_info['color_title_4'];
        } else {
            $data['color_title_4'] = '';
        }

        if (isset($this->request->post['right_link'])) {
            $data['right_link'] = $this->request->post['right_link'];
        } elseif (!empty($module_info)) {
            $data['right_link'] = $module_info['right_link'];
        } else {
            $data['right_link'] = '';
        }

        if (isset($this->request->post['right_text_1'])) {
            $data['right_text_1'] = $this->request->post['right_text_1'];
        } elseif (!empty($module_info)) {
            $data['right_text_1'] = $module_info['right_text_1'];
        } else {
            $data['right_text_1'] = '';
        }
        if (isset($this->request->post['right_text_2'])) {
            $data['right_text_2'] = $this->request->post['right_text_2'];
        } elseif (!empty($module_info)) {
            $data['right_text_2'] = $module_info['right_text_2'];
        } else {
            $data['right_text_2'] = '';
        }
        if (isset($this->request->post['right_text_3'])) {
            $data['right_text_3'] = $this->request->post['right_text_3'];
        } elseif (!empty($module_info)) {
            $data['right_text_3'] = $module_info['right_text_3'];
        } else {
            $data['right_text_3'] = '';
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

        $this->response->setOutput($this->load->view('extension/module/block_recipes', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/block_recipes')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
            $this->error['name'] = $this->language->get('error_name');
        }

        return !$this->error;
    }
}
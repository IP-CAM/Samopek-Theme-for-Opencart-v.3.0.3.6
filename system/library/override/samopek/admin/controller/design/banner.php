<?php
class samopek_ControllerDesignBanner extends ControllerDesignBanner {
    protected function getForm() {
        $data['text_form'] = !isset($this->request->get['banner_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

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

        if (isset($this->error['banner_image'])) {
            $data['error_banner_image'] = $this->error['banner_image'];
        } else {
            $data['error_banner_image'] = array();
        }

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('design/banner', 'user_token=' . $this->session->data['user_token'] . $url, true)
        );

        if (!isset($this->request->get['banner_id'])) {
            $data['action'] = $this->url->link('design/banner/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        } else {
            $data['action'] = $this->url->link('design/banner/edit', 'user_token=' . $this->session->data['user_token'] . '&banner_id=' . $this->request->get['banner_id'] . $url, true);
        }

        $data['cancel'] = $this->url->link('design/banner', 'user_token=' . $this->session->data['user_token'] . $url, true);

        if (isset($this->request->get['banner_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $banner_info = $this->model_design_banner->getBanner($this->request->get['banner_id']);
        }

        $data['user_token'] = $this->session->data['user_token'];

        if (isset($this->request->post['name'])) {
            $data['name'] = $this->request->post['name'];
        } elseif (!empty($banner_info)) {
            $data['name'] = $banner_info['name'];
        } else {
            $data['name'] = '';
        }

        if (isset($this->request->post['status'])) {
            $data['status'] = $this->request->post['status'];
        } elseif (!empty($banner_info)) {
            $data['status'] = $banner_info['status'];
        } else {
            $data['status'] = true;
        }

        $this->load->model('localisation/language');

        $data['languages'] = $this->model_localisation_language->getLanguages();

        $this->load->model('tool/image');

        if (isset($this->request->post['banner_image'])) {
            $banner_images = $this->request->post['banner_image'];
        } elseif (isset($this->request->get['banner_id'])) {
            $banner_images = $this->model_design_banner->getBannerImages($this->request->get['banner_id']);
        } else {
            $banner_images = array();
        }

        $data['banner_images'] = array();

        foreach ($banner_images as $key => $value) {
            foreach ($value as $banner_image) {
                if (is_file(DIR_IMAGE . $banner_image['image'])) {
                    $image = $banner_image['image'];
                    $thumb = $banner_image['image'];
                } else {
                    $image = '';
                    $thumb = 'no_image.png';
                }

                $data['banner_images'][$key][] = array(
                    'title'      => $banner_image['title'],
                    'link'       => $banner_image['link'],
                    'description' => $banner_image['description'],
                    'image'      => $image,
                    'thumb'      => $this->model_tool_image->resize($thumb, 100, 100),
                    'sort_order' => $banner_image['sort_order']
                );
            }
        }

        $data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('design/banner_form', $data));
    }
}
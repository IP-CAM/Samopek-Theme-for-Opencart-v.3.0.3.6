<?php
class ControllerExtensionModulePromo extends Controller {
    public function index($setting) {
        static $module = 0;

        $this->load->model('design/banner');
        $this->load->model('tool/image');

        $data['banners'] = array();

        $results = $this->model_design_banner->getBanner($setting['banner_id']);

        foreach ($results as $result) {
            if (is_file(DIR_IMAGE . $result['image'])) {
                $width = ($result['sort_order'] == 3) ? 408 : 555;
                $height = ($result['sort_order'] == 3) ? 507 : 329;
                $data['banners'][] = array(
                    'title' => html_entity_decode($result['title'], ENT_QUOTES, 'UTF-8'),
                    'link'  => $result['link'],
                    'description'  => html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'),
                    'image' => $this->model_tool_image->resize($result['image'], $width, $height)
                );
            }
        }

        $data['module'] = $module++;

        return $this->load->view('extension/module/promo', $data);
    }
}
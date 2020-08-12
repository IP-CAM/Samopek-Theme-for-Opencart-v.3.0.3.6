<?php
class ControllerExtensionModuleBlockRecipes extends Controller {
    public function index($setting) {
        $this->load->model('design/banner');
        $this->load->model('tool/image');

        $data['banners'] = array();

        $results = $this->model_design_banner->getBanner($setting['banner_id']);

        foreach ($results as $result) {
            if (is_file(DIR_IMAGE . $result['image'])) {
                $data['banners'][] = array(
                    'title' => html_entity_decode($result['title'], ENT_QUOTES, 'UTF-8'),
                    'link'  => $result['link'],
                    'description'  => html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'),
                    'image' => $this->model_tool_image->resize($result['image'], 438, 324)
                );
            }
        }

        $data['title'] = html_entity_decode($setting['name'], ENT_QUOTES, 'UTF-8');
        $data['color_title_1'] = html_entity_decode($setting['color_title_1'], ENT_QUOTES, 'UTF-8');
        $data['color_title_2'] = html_entity_decode($setting['color_title_2'], ENT_QUOTES, 'UTF-8');
        $data['color_title_3'] = html_entity_decode($setting['color_title_3'], ENT_QUOTES, 'UTF-8');
        $data['color_title_4'] = html_entity_decode($setting['color_title_4'], ENT_QUOTES, 'UTF-8');
        $data['right_link'] = html_entity_decode($setting['right_link'], ENT_QUOTES, 'UTF-8');
        $data['right_text_1'] = html_entity_decode($setting['right_text_1'], ENT_QUOTES, 'UTF-8');
        $data['right_text_2'] = html_entity_decode($setting['right_text_2'], ENT_QUOTES, 'UTF-8');
        $data['right_text_3'] = html_entity_decode($setting['right_text_3'], ENT_QUOTES, 'UTF-8');

        return $this->load->view('extension/module/block_recipes', $data);
    }
}
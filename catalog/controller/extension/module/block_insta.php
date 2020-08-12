<?php
class ControllerExtensionModuleBlockInsta extends Controller {
	public function index($setting) {
    $data['title'] = html_entity_decode($setting['title'], ENT_QUOTES, 'UTF-8');
    $data['description'] = html_entity_decode($setting['description'], ENT_QUOTES, 'UTF-8');
    $data['link_text'] = html_entity_decode($setting['link_text'], ENT_QUOTES, 'UTF-8');
    $data['link'] = html_entity_decode($setting['link'], ENT_QUOTES, 'UTF-8');
    $data['vk_link'] = html_entity_decode($setting['vk_link'], ENT_QUOTES, 'UTF-8');
    $data['insta_link'] = html_entity_decode($setting['insta_link'], ENT_QUOTES, 'UTF-8');

    for ($i = 1; $i <= 9; $i++) {
      $data['post_' . $i] = html_entity_decode($setting['post_' . $i], ENT_QUOTES, 'UTF-8');
      $data['post_' . $i . '_image'] = html_entity_decode($setting['post_' . $i . '_image'], ENT_QUOTES, 'UTF-8');
    }
		return $this->load->view('extension/module/block_insta', $data);
	}
}

<?php
class samopek_ControllerCommonHeader extends ControllerCommonHeader {
    public function preRender( $template_buffer, $template_name, &$data ) {
        if ($template_name != $this->config->get( 'config_theme' ).'/template/common/header.twig') {
            return parent::preRender( $template_buffer, $template_name, $data );
        }
        $data['delivery'] = $this->url->link('information/information&information_id=6');
        $data['home_page'] = false;
        if ($this->request->server['REQUEST_METHOD'] == 'GET') {
            //error_log($this->request->get['route']);
            if (!isset($this->request->get['route']) || strpos($this->request->get['route'], "home") !== false) {
                $data['home_page'] = true;
            }
        }
        error_log("assad");
        error_log($data['home_page']);
        return parent::preRender($template_buffer, $template_name, $data);
    }
}
?>
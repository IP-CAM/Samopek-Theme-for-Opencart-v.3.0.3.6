<?php
class samopek_ControllerCommonHeader extends ControllerCommonHeader {
    public function preRender( $template_buffer, $template_name, &$data ) {
        if ($template_name != $this->config->get( 'config_theme' ).'/template/common/header.twig') {
            return parent::preRender( $template_buffer, $template_name, $data );
        }
        $data['delivery'] = $this->url->link('information/information&information_id=6');
        return parent::preRender($template_buffer, $template_name, $data);
    }
}
?>
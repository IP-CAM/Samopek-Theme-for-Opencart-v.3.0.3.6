<?php
class samopek_ControllerMailRegister extends ControllerMailRegister {

    public function alert(&$route, &$args, &$output) {
        if (!isset($args[0]['lastname'])) {
            $args[0]['lastname'] = "";
        }
        if (!isset($args[0]['telephone'])) {
            $args[0]['telephone'] = "";
        }
        return parent::alert($route, $args, $output);
    }
}
?>
<?php
class ControllerProductProductPreview extends Controller {
    public function index()
    {
        $data['product_id'] = 1;
        return $this->load->view('product/product_preview', $data);
    }
}
?>
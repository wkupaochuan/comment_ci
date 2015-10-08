<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class index extends CI_Controller {

    public function __construct(){
        parent::__construct();
    }

	public function index()
	{}

    public function convert_page()
    {
        $this->load->view('shorturl/index/convert_page.php');
    }
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
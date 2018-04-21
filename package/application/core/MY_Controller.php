<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require_once(APPPATH . "core/Security_Controller.php");

class MY_Controller extends Security_Controller
{
    public function __construct()
    {
        parent::__construct();
    }
}
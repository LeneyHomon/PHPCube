<?php
class index extends Controller_Base
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->_view('index');
    }
}
<?php

//控制器基层
class Controller_Base
{
    /**
     * @var View_Base
     */
    private $_view;
    //模板目录
    protected $_mould = null;
    //模板文件
    protected $_mouldFile = '__mould';
    //是否使用模板
    protected $_useMould = true;
    //显示到视图的变量
    protected $_viewData = array();


    public function __construct()
    {
        $this->_view = new View_Base();
        $this->_mould = 'mould';
    }

    /**
     * 设置模板文件
     *
     * @param $file
     */
    protected function setMould($file)
    {
        if(empty($file)) {
            return ;
        }
        $file = trim($file, '/');
        $paths = explode('/', $file);
        $floor = count($paths);

        if($floor > 1) {
            //传入的是带文件夹的路径
            $this->_mouldFile = $paths[$floor - 1];
            unset($paths[$floor - 1]);
            $this->_mould = implode('/', $paths);
        } else {
            //不带文件夹的路径
            $this->_mouldFile = $paths[0];
        }
    }

    /**
     * 显示视图
     *
     * @param $file string 相对于view文件夹的相对路径
     * @param $mould boolean 是否使用模板
     */
    protected function _view($file, $mould = true)
    {
        if($mould === false) {
            $this->_useMould = false;
        }
        echo $this->_viewBase($file);
    }

    /**
     * 输出视图
     *
     * @param $file
     *
     * @return string
     */
    private function _viewBase($file)
    {
        $viewFile = APP_VIEW_PATH . DS . $file . PHP_EXT;
        if($this->_useMould) {
            $mouldFile = APP_VIEW_PATH . DS . $this->_mould . DS . $this->_mouldFile . PHP_EXT;
        } else {
            $mouldFile = false;
        }

        return $this->_view->display($viewFile, $this->_viewData, $mouldFile);
    }
}
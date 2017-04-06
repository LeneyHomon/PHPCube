<?php

//视图基层，处理视图文件
class View_Base
{
    public function __construct()
    {
    }

    /**
     * 显示视图
     *
     * @param string $tpl 视图文件路径
     * @param bool|mixed $data 输出到模板的数据
     * @param bool|string $mould 模板文件路径
     *
     * @return string
     */
    public function display($tpl, $data, $mould = false)
    {
        ob_start();
        ob_implicit_flush(0);
        if($data) {
            extract($data, EXTR_OVERWRITE);
        }

        if(file_exists($tpl)) {
            include $tpl;
        } else {
        }
        $__content = ob_get_clean();

        if($mould) {
            ob_start();
            ob_implicit_flush(0);
            if(file_exists($mould)) {
                include $mould;
            } else {
            }
            $__content = ob_get_clean();
        }

        $content = $__content;
        return $content;
    }
}
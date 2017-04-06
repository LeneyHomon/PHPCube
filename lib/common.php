<?php
if(!defined('PHP_EXT')) {
    define('PHP_EXT', '.php');
}

/**
 * Ajax方式返回数据
 *
 * @param mixed  $data 要返回的数据
 * @param string $type 返回数据格式
 */
function ajax_return($data, $type = 'JSON')
{
    switch($type) {
        case 'JSON':
            header('Content-Type:application/json; charset=utf-8');
            echo json_encode($data);
            die();
        case 'JSONP':
            // 返回JSON数据格式到客户端 包含状态信息
            header('Content-Type:application/json; charset=utf-8');
            $handler = isset($_GET['callback']) ? $_GET['callback'] : 'jsonpReturn';
            // exit ( $handler . '(' . json_encode ( $data ) . ');' );
            echo $handler . '(' . json_encode($data) . ');';
            die();
    }
}
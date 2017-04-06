# PHPCube
小巧简单的自用PHP框架
* 单一入口
* 链式操作ORM
* M层与项目分离，可服务于多个项目

##目录 
    config    配置目录
        └config.php    主配置文件
        
    lib    库目录
        ├extra    扩展库目录
        ├system    系统必须（框架必须）目录
        └common.php    公共方法
        
    project    项目目录
        └www.test.com    
            ├application    <<<网站后台目录>>>
            │   ├config
            │   ├controller
            │   ├lib
            │   └view
            │
            ├www    <<<网站根目录>>>
            │   ├css
            │   ├img
            │   ├js
            │   └index.php    <<<网站入口文件>>>
            │
            └bootstrap.php    引导文件
    
    service    服务层(M层）目录
        
    bootstrap.php    引导文件

##持续更新中...
Email: leneyhomon@gmail.com
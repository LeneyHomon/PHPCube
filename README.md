# PHPCube
舍弃大框架复杂冗余的库类，小巧的PHP框架，用于帮助个人小型网站、小型项目的搭建。
* 单一入口
* 链式操作ORM
* M层与项目分离，可服务于多个项目

## 文档
[PHPCube](http://lab.leneyhomon.com/phpcube/index)
## 目录 
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

## 缓慢更新中......
* 2017/4/7 优化SQL语句构造方式
* 2017/5/25 更新框架文档 
 
Email: leneyhomon@gmail.com
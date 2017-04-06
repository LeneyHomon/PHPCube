<?php
class index extends Controller_Base
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        //_viewData存储显示到视图中的变量
        $this->_viewData['result'] = '点击链接开始测试';
        $this->_view('index');
    }

    public function add()
    {
        $Teacher = new Model_Teacher();

        $teacher = array(
            'name' => 'Mr.One',
        );
        $result = $Teacher->addTeacher($teacher);

        $this->_viewData['result'] = $result;
        $this->_view('index');
    }

    public function getAll()
    {
        $Teacher = new Model_Teacher();
        $teachers = $Teacher->getAll();

        $this->_viewData['result'] = $teachers;
        $this->_view('index');
    }

    public function save()
    {
        $Teacher = new Model_Teacher();
        $teacher = array(
            'id' => 1,
            'name' => 'Mr.Yang'
        );
        $result = $Teacher->saveTeacher($teacher);

        $this->_viewData['result'] = $result;
        $this->_view('index');
    }

    public function del()
    {
        $Teacher = new Model_Teacher();
        $id = 1;
        $result = $Teacher->delTeacher($id);

        $this->_viewData['result'] = $result;
        $this->_view('index');
    }
}
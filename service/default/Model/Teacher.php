<?php

class Model_Teacher extends Db_Model
{
    public function __construct()
    {
        parent::__construct('d_school', 't_teacher');
    }

    public function addTeacher($data)
    {
        if(empty($data)) {
            return false;
        }

        return $this->data($data)->insert();
    }

    public function getAll()
    {
        return $this->select();
    }

    public function saveTeacher($data)
    {
        if(empty($data)) {
            return false;
        }
        $where = array(
            'id' => $data['id']
        );

        return $this->data($data)->where($where)->update();
    }

    public function delTeacher($id)
    {
        $where = array(
            'id' => $id
        );

        //debug操作，返回SQL语句而不执行。
        return $this->debug()->where($where)->delete();
    }
}
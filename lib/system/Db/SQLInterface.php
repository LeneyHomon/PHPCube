<?php
interface Db_SQLInterface
{
    public function setTable($table);

    public function insert($data, $multiple);
    public function select();
    public function update($data);
    public function delete();
    public function where($where);
    public function field($fields);
    public function order($order);
    public function limit($count, $start);
    public function group($group);
}
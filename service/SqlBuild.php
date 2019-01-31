<?php

namespace App\service;

class SqlBuild
{

    /**
     * @param $table
     * @param $colmuns
     * @param $where
     * @return string
     */
    public static function update($table, $colmuns, $where)
    {
        $sqlBeign = "UPDATE {$table} SET ";
        $set = '';
        foreach ($colmuns as $colmun=> $v) {
            $set .= " `{$colmun}` = '{$v}' ,";
        }
        $set = rtrim($set, ',');

        // where
        $condition = 'WHERE';
        foreach ($where as $key => $v) {
            $condition .= " `{$key}` = '{$v}' AND";
        }
        $condition = rtrim($condition, 'AND');
        return $sqlBeign . $set . $condition;
    }
}
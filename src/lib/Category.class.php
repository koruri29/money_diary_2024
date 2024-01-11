<?php

namespace lib;

use lib\common\PDODatabase;

class Category
{
    private PDODatabase $db;

    private int $category_id;
    
    private int $user_id;
    
    private string $category_name;
   
    private int $item_order;
   
    private int $icon_id;
   
    private string $icon_color;


    // public function __construct(PDODatabase $db)
    // {
    //     $this->$db = $db;
    // }

    public function getCategoriesByUserId(int $user_id) : array
    {
        $this->db->resetClause();

        $table = ' categories c ';
        $column = <<<COL
            c.id AS category_id,
            c.category_name,
            c.item_order,
            c.icon_id,
            c.icon_color,
            i.html_tag AS i_html 
        COL;
        $where = ' c.user_id = ? ';
        $arr_val = [$user_id];

        $join_table = ' icons i';
        $join_on = 'i.id = c.icon_id ';

        $this->db->pushJoin($join_table, $join_on);
        
        $categories = $this->db->select($table, $column, $where, $arr_val);

        return $categories;
    }

    public function setDb(PDODatabase $db)
    {
        $this->db = $db;
    }
}

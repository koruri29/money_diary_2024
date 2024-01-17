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

    public static function getCategoriesByUserId (PDODatabase $db, int $user_id) : array
    {
        $db->resetClause();

        $table = ' categories c ';
        $column = <<<COL
            c.id AS category_id,
            c.category_name,
            c.item_order,
            c.icon_id,
            c.icon_color,
            i.html_tag AS i_html,
            i.icon_name 
        COL;
        $where = ' c.user_id = ? ';
        $arr_val = [$user_id];

        $join_table = ' icons i';
        $join_on = 'i.id = c.icon_id ';

        $db->pushJoin($join_table, $join_on);
        
        $categories = $db->select($table, $column, $where, $arr_val);

        return $categories;
    }

    public static function initCategories(PDODatabase $db, int $user_id) : bool {
        $table = ' categories ';
        $insertDataCol = [
            'user_id',
            'category_name',
            'item_order',
            'icon_id',
            'icon_color',
        ];
        $insertDataValArr = [
            [$user_id, '支出', 999, 2, '#dc143c'],
            [$user_id, '収入', 998, 1, '#4682b4'],
            [$user_id, '食費', 3, 3, '#ffa500'],
            [$user_id, '日用品', 4, 19, '#ff6347'],
            [$user_id, '交通費', 5, 9, '#00008b'],
            [$user_id, '趣味', 6, 17, '#008000'],
            [$user_id, '交際費', 7, 48, '#d2b48c'],
            [$user_id, '車', 8, 10, '#40e0d0'],
            [$user_id, '住居', 9, 27, '#8fbc8f'],
            [$user_id, 'その他', 10, 77, '#b0c4de'],
        ];

        $res = $db->repeatInsert($table, $insertDataCol, $insertDataValArr);

        return $res;
    }

    public function setDb(PDODatabase $db)
    {
        $this->db = $db;
    }
}

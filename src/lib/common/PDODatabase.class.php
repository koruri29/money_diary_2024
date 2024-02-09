<?php

namespace lib\common;

class PDODatabase
{
    public $dbh = null;

    private string $db_host = '';

    private string $db_user = '';
    
    private string $db_pass = '';
    
    private string $db_name = '';

    private string $order = '';

    private string $limit = '';

    private string $offset = '';
    
    private string $group_by = '';

    private array $joins = [];

    private array $sql_errors = [];


    public function __construct(string $db_host, string $db_user, string $db_pass, string $db_name)
    {
        $this->dbh = $this->connectDB($db_host, $db_user, $db_pass, $db_name);
        $this->db_host = $db_host;
        $this->db_user = $db_user;
        $this->db_pass = $db_pass;
        $this->db_name = $db_name;
    }

    private function connectDB(string $db_host, string $db_user, string $db_pass, string $db_name): \PDO
    {
        $opt = array(
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::MYSQL_ATTR_MULTI_STATEMENTS => false,
            \PDO::ATTR_EMULATE_PREPARES => false,
        );

        try {
            $dsn = 'mysql:host=' . $db_host . ';dbname=' . $db_name;
            $dbh = new \PDO($dsn, $db_user, $db_pass, $opt);
            // $dbh->setAttribute(PDO::MYSQL_ATTR_MULTI_STATEMENTS, false);
            $dbh->query('SET NAMES utf8');

        } catch (\PDOException $e) {
            var_dump($e->getMessage());
            exit();   
        }

        return $dbh;
    }

    public function select(string $table, string $column = '', string $where = '', array $arr_val = []): array
    {
        $sql = $this->getSql('select', $table, $where, $column);
// echo '___' . $sql . '<BR>'   ;
        $this->sqlLogInfo($sql, $arr_val);

        $stmt = $this->dbh->prepare($sql);
        $res = $stmt->execute($arr_val);

        if ($res === false) {
            $this->catchError($stmt->errorInfo());
        }

        $data = [];
        while ($result = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            array_push($data, $result);
        }
        return $data;
    }

    /**
     * 引数からSQLを組み立てる
     * 
     * @param string $type SQLの命令部分を指定
     * @param string $table テーブルを指定
     * @param string $where WHERE句を指定
     * @param string $column カラム名を指定
     * @return string $sql SQL文
     */
    private function getSql(string $type, string $table, string $where = '', string $column = ''): string
    {
        switch ($type) {
            case 'select':
                $column_key = ($column !== '') ? $column :'*';
                break;

            case 'count':
                $column_key = 'COUNT(*) AS NUM';
                break;

            default:
                break;
        }

        $where_sql = ($where !== '') ? 'WHERE ' . $where : ' ';
        $join = implode(' ', $this->joins);
        $other = $this->group_by . ' ' . $this->order . ' ' . $this->limit . ' ' . $this->offset;

        $sql = 'SELECT ' . $column_key . 'FROM ' . $table . ' ' . $join . ' ' . $where_sql . ' ' . $other;
        return $sql;
    }

    /**
     * 前のSQLの句を削除
     * 
     * @return void
     */
    public function resetClause() : void
    {
        $this->order = '';

        $this->limit = '';
    
        $this->offset = '';
        
        $this->group_by = '';
    
        $this->joins = [];
    }

    public function setOrder(string $order = ''): void
    {
        if ($order !== '') {
            $this->order = 'ORDER BY ' . $order;
        }
    }

    public function setLimitOff(string $limit = '', string $offset = ''): void
    {
        if ($limit !== '') {
            $this->limit = " LIMIT " . $limit;
        }
        if ($offset !== '') {
            $this->offset = " OFFSET " . $offset;
        }
    }

    public function setGroupBy(string $group_by): void
    {
        if ($group_by !== '') {
            $this->group_by = 'GROUP BY ' . $group_by;
        }
    }

    public function pushJoin(string $table, string $on): void
    {
        $this->joins[] = ' INNER JOIN ' . $table . ' ON ' . $on . ' ';
    }


    public function insert(string $table, array $insert_data = []): bool
    {
        $insert_data_key = [];
        $insert_data_val = [];
        $preCnt = [];//プリペアードステートメントのカウント用

        $columns = '';
        $pre_st = '';//プリペアードステートメント

        foreach ($insert_data as $key => $val) {
            $insert_data_key[] = $key;
            $insert_data_val[] = $val;
            $preCnt[] = '?';
        }

        $columns = implode(',', $insert_data_key);
        $pre_st = implode(',', $preCnt);

        $sql = 'INSERT INTO '
        . $table
        . ' ('
        . $columns
        . ') VALUES ('
        . $pre_st
        . ')';

        $this->sqlLogInfo($sql, $insert_data_val);

        $stmt = $this->dbh->prepare($sql);
        $res = $stmt->execute($insert_data_val);

        if ($res === false) {
            $this->catchError($stmt->errorInfo());
        }

        return $res;
    }

    public function repeatInsert(string $table, array $insert_data_col, array $insert_data_val_arr) :bool
    {
        foreach ($insert_data_col as $val) {
            $preCnt[] = '?';
        }

        $columns = implode(',', $insert_data_col);
        $pre_st = implode(',', $preCnt);

        $sql = 'INSERT INTO '
        . $table
        . ' ('
        . $columns
        . ') VALUES ('
        . $pre_st
        . ')';

        $flg = true;
        foreach ($insert_data_val_arr as $insert_data_val) {
            $this->sqlLogInfo($sql, $insert_data_val);
            // echo $sql;
            $stmt = $this->dbh->prepare($sql);
            $res = $stmt->execute($insert_data_val);
            if (! $res) $flg = false;
        }

        if ($res === false) {
            $this->catchError($stmt->errorInfo());
        }

        return $flg;
    }

    public function update(string $table, array $insert_data = [], string $where = '', array $arr_where_val = []): bool
    {
        $arr_pre_st = [];//プリペアードステートメントを配列で準備

        foreach($insert_data as $col => $val) {
            $arr_pre_st[] = $col . ' = ? ';
        }
        $pre_st = implode(',', $arr_pre_st);

        $sql = "UPDATE "
            . $table
            . " SET "
            . $pre_st
            . " WHERE "
            . $where;
        
            $update_data = array_merge(array_values($insert_data), $arr_where_val);
            $this->sqlLogInfo($sql, $update_data);
echo $sql;
var_dump($update_data);
            $stmt = $this->dbh->prepare($sql);
            $res = $stmt->execute($update_data);

            if ($res === false) {
                $this->catchError($stmt->errorInfo());
            }

            return $res;
    }

    public function delete(string $table, array $delete_val_arr) : bool
    {
        $col_arr = [];
        $arr_val = [];
        foreach ($delete_val_arr as $col => $val) {
            $col_arr[] = $col . ' = ? ';
            $col_arr[] = ' AND ';
            $arr_val[] = $val;
            $arr_val[] = ' AND ';
        }
        array_pop($col_arr);
        array_pop($arr_val);

        $pre_st = implode(',', $col_arr);
        $pre_stVal = implode(',', $arr_val);

        $sql = "DELETE FROM "
        . $table
        . " WHERE "
        . $pre_st;

        $stmt = $this->dbh->prepare($sql);
        $res = $stmt->execute($arr_val);

        return $res;
    }

    /**
     * 最後にテーブルに挿入した行のIDを取得する
     * 
     * @return int
     */
    public function getLastId()
    {
        return $this->dbh->lastInsertId();
    }

    /**
     * クエリが失敗したときにエラー表示をし、処理を終了させる
     * @param array $errArr PDOStatement::errorInfoの戻り値
     * @return void
     */
    private function catchError(array $errArr = []): void
    {
        $errMsg = (! empty($errArr[2])) ? $errArr[2] : '';
        $this->sql_errors[] = $errMsg;
    }

    private function makeLogFile(): string
    {
        $logDir = dirname(__DIR__) . '/../../logs';
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777);
        }
        $log_path = $logDir . '/general.log';
        if (!file_exists($log_path)) {
            touch($log_path);
        }
        return $log_path;
    }

    /**
     * SQLとそれにバインドする値をログに記録
     * 
     * @param string $str SQL(プリペアードステートメント)
     * @param array $arr_val バインドする値
     * @return void
     */
    private function sqlLogInfo(string $sql, array $arr_val = []): void
    {
        $log_path = $this->makeLogFile();
    $log_data = sprintf("[SQL_LOG:%s]: %s [%s]\n", date('Y-m-d H:i:s'), $sql, 'test'/*implode(',', $arr_val)*/);
        error_log($log_data, 3, $log_path);
    }

    public function getSqlErrors() : array
    {
        return $this->sql_errors;
    }
}

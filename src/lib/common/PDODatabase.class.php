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
    
    private string $groupby = '';

    private array $joins = [];


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
            $dbh->query('SET NAMES utf8');

        } catch (\PDOException $e) {
            var_dump($e->getMessage());
            exit();   
        }

        return $dbh;
    }

    public function select(string $table, string $column = '', string $where = '', array $arrVal = []): array
    {
        $sql = $this->getSql('select', $table, $where, $column);
// echo $sql . '<BR>';
        $this->sqlLogInfo($sql, $arrVal);

        $stmt = $this->dbh->prepare($sql);
        $res = $stmt->execute($arrVal);

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
                $columnKey = ($column !== '') ? $column :'*';
                break;

            case 'count':
                $columnKey = 'COUNT(*) AS NUM';
                break;

            default:
                break;
        }

        $whereSql = ($where !== '') ? 'WHERE ' . $where : ' ';
        $join = implode(' ', $this->joins);
        $other = $this->groupby . ' ' . $this->order . ' ' . $this->limit . ' ' . $this->offset;

        $sql = 'SELECT ' . $columnKey . 'FROM ' . $table . ' ' . $join . ' ' . $whereSql . ' ' . $other;
        return $sql;
    }

    public function resetClause() : void
    {
        $this->order = '';

        $this->limit = '';
    
        $this->offset = '';
        
        $this->groupby = '';
    
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

    public function setGroupBy(string $groupby): void
    {
        if ($groupby !== '') {
            $this->groupby = 'GROUP BY ' . $groupby;
        }
    }

    public function pushJoin(string $table, string $on): void
    {
        $this->joins[] = ' INNER JOIN ' . $table . ' ON ' . $on . ' ';
    }


    public function insert(string $table, array $insertData = []): bool
    {
        $insertDataKey = [];
        $insertDataVal = [];
        $preCnt = [];//プリペアードステートメントのカウント用

        $columns = '';
        $preSt = '';//プリペアードステートメント

        foreach ($insertData as $key => $val) {
            $insertDataKey[] = $key;
            $insertDataVal[] = $val;
            $preCnt[] = '?';
        }

        $columns = implode(',', $insertDataKey);
        $preSt = implode(',', $preCnt);

        $sql = 'INSERT INTO '
        . $table
        . ' ('
        . $columns
        . ') VALUES ('
        . $preSt
        . ')';

        $this->sqlLogInfo($sql, $insertDataVal);
// echo $sql;
        $stmt = $this->dbh->prepare($sql);
        $res = $stmt->execute($insertDataVal);

        if ($res === false) {
            $this->catchError($stmt->errorInfo());
        }

        return $res;
    }

    public function update(string $table, array $insertData = [], string $where = '', array $arrWhereVal = []): bool
    {
        $arrPreSt = [];//プリペアードステートメントを配列で準備

        foreach($insertData as $col => $val) {
            $arrPreSt[] = $col . ' = ? ';
        }
        $preSt = implode(',', $arrPreSt);

        $sql = "UPDATE "
            . $table
            . " SET "
            . $preSt
            . " WHERE "
            . $where;
        
            $updateData = array_merge(array_values($insertData), $arrWhereVal);
            $this->sqlLogInfo($sql, $updateData);

            $stmt = $this->dbh->prepare($sql);
            $res = $stmt->execute($updateData);

            if ($res === false) {
                $this->catchError($stmt->errorInfo());
            }

            return $res;
    }

    public function delete(string $table, string $column, int $id) : bool
    {
        $sql = 'DELETE FROM ? WHERE ? = ?';
        $arrVal = [$table, $column, $id];

        $stmt = $this->dbh->prepare($sql);
        $res = $stmt->execute($arrVal);

        return $res;
    }


    /**
     * クエリが失敗したときにエラー表示をし、処理を終了させる
     * @param array $errArr PDOStatement::errorInfoの戻り値
     * @return void
     */
    private function catchError(array $errArr = []): void
    {
        $errMsg = (!empty($errArr[2])) ? $errArr[2] : '';
        exit('SQLエラーが発生しました。 ' . $errMsg);
    }

    private function makeLogFile(): string
    {
        $logDir = dirname(__DIR__) . '/../../logs';
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777);
        }
        $logPath = $logDir . '/general.log';
        if (!file_exists($logPath)) {
            touch($logPath);
        }
        return $logPath;
    }

    /**
     * SQLとそれにバインドする値をログに記録
     * 
     * @param string $str SQL(プリペアードステートメント)
     * @param array $arrVal バインドする値
     * @return void
     */
    private function sqlLogInfo(string $sql, array $arrVal = []): void
    {
        $logPath = $this->makeLogFile();
        $logData = sprintf("[SQL_LOG:%s]: %s [%s]\n", date('Y-m-d H:i:s'), $sql, implode(',', $arrVal));
        error_log($logData, 3, $logPath);
    }
}

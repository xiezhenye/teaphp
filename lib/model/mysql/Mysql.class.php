<?php


/**
 * Mysql数据库操作类
 * 
 * 
 */
class Mysql {
    protected $db;
    protected $host; 
    protected $name;
    protected $charset;
    
    /**
     *
     * @param array $conf 设置 $conf 参数时，会根据参数连接数据库
     */
    function __construct($conf = null) {
        if (!is_null($conf)) {
            $this->connect($conf['host'], $conf['username'], $conf['password'], $conf['name'], $conf['charset']);
        }
    }
    
    /**
     * 连接数据库
     *
     * @param string $host mysql主机地址端口：e.g. 127.0.0.1:3306
     * @param string $username 用户名
     * @param string $password 密码
     * @param string $name 数据库名
     * @param string $charset 字符集
     */
    public function connect($host, $username, $password, $name, $charset = 'utf8') {
        $this->db = mysql_connect($host, $username, $password, true);
        $this->host = $host;
        $this->name = $name;
        $this->charset = $charset;
        $this->selectDB($name);
        mysql_query("set names '$charset'", $this->db);
    }
    
    public function selectDB($name) {
        mysql_select_db($name, $this->db);
    }
    
    /**
     * 执行sql查询
     * 
     * @param string $sql
     * @return MysqlResultSet 结果集
     */
    public function query($sql) {
        $rs = mysql_query($sql, $this->db);
        if (!$rs) {
            $message = '['.$this->host.' '.$this->name.'] '.
                       mysql_error($this->db)."\nsql: $sql";
            throw(new Exception($message));
        }
        return new MysqlResultSet($rs);
    }
    
    /**
     * 转义特殊字符
     *
     * @param string $str
     * @return string
     */
    public function quote($str) {
        return mysql_real_escape_string($str, $this->db);
    }
    
    /**
     * 执行一个Sql语句
     *
     * @param string $sql
     * return mixed
     */
    public function execute($sql) {
        $ret = mysql_query($sql, $this->db);
        if (!$ret) {
            $message = '['.$this->host.' '.$this->name.'] '.
                       mysql_error($this->db)."\nsql: $sql";
            throw(new Exception($message));
        }
        return $ret;
    }
    
    /**
     * 添加字段、表名等的分界符(反引号)
     * 
     * @param string $s
     * return string
     */
    function addDelimiter($s) {
        return "`$s`";
    }
    
    /**
     * 取得上一条语句影响的行数
     *
     * @return int
     */
    function affected() {
        return mysql_affected_rows($this->db);
    }
    
    function lastId() {
        return mysql_insert_id($this->db);
    }
	
    /**
     * 为 sql 语句添加 limit 子句
     *
     * @param string $sql 原始 sql 语句
     * @param int $limit
     * @param int $offset
     * @return string
     */
    public function addLimitClause($sql, $limit, $offset = 0) {
        return $sql . ' limit ' . intval($offset) . ',' . intval($limit);
    }
    
    function errorMessage() {
        return mysql_error() . '(' . mysql_errno() . ')';
    }
    
    function getResource() {
        return $this->db;
    }
}

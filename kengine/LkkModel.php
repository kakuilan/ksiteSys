<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/9/17
 * Time: 12:32
 * Desc: -lkk 数据模型类
 */


namespace Kengine;

use Kengine\LkkCmponent;
use Lkk\Helpers\ArrayHelper;
use Lkk\Helpers\EncryptHelper;
use Lkk\LkkMacAddress;
use Lkk\Phalwoo\Phalcon\Paginator\Adapter\AsyncMysql as PaginatorAsyncMysql;
use Lkk\Phalwoo\Server\SwooleServer;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Query\Builder as QueryBuilder;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class LkkModel extends Model {

    //数据查询表达式
    public static $exp = [
        'eq' => '=',
        'neq' => '<>',
        'gt' => '>',
        'egt' => '>=',
        'lt' => '<',
        'elt' => '<=',
        'notlike' => 'NOT LIKE',
        'like' => 'LIKE',
        'in' => 'IN',
        'notin' => 'NOT IN',
        'not in' => 'NOT IN',
        'between' => 'BETWEEN',
        'not between' => 'NOT BETWEEN',
        'notbetween' => 'NOT BETWEEN',
        'null' => 'IS NULL',
        'isnull' => 'IS NULL',
        'is null' => 'IS NULL',
        'notnull' => 'IS NOT NULL',
        'is not null' => 'IS NOT NULL',
        'exists' => 'EXISTS',
        'notexists' => 'NOT EXISTS',
        'not exists' => 'NOT EXISTS',
    ];

    //ESCAPE statement string
    public static $_like_escape_str = " ESCAPE '%s' ";

    //ESCAPE character
    public static $_like_escape_chr = '!';


    /**
     * 缓存数据表的字段数组
     * @var
     */
    public static $tableColumns;


    /**
     * 初始化
     */
    public function initialize (){
        $this->setReadConnectionService('dbSlave');
        $this->setWriteConnectionService('dbMaster');

        //统一设置模型对应的数据表名,表名小写,例如 模型类AbcEfg => lkk_abc_efg表i
        $table = self::getTableName();
        $this->setSource($table);
    }


    /**
     * Gets the connection used to read data for the model 重写
     *
     * @return \Phalcon\Db\AdapterInterface
     */
    public function getReadConnection() {
        return LkkCmponent::SyncDbSlave();
    }


    /**
     * Gets the connection used to write data to the model 重写
     *
     * @return \Phalcon\Db\AdapterInterface
     */
    public function getWriteConnection() {
        return LkkCmponent::SyncDbMaster();
    }



    /**
     * 获取表名
     * @return mixed|string
     */
    public static function getTableName($table='') {
        $prefix = getConf('pool','mysql_master')['table_prefix'];
        if(empty($table)) {
            $class = explode('\\', get_called_class());
            $table = end($class);
        }

        $array = array_filter(preg_split("/(?=[A-Z])/",$table));
        $table = (count($array)==1) ? $table : implode('_', $array);
        $table = strtolower($prefix . $table);

        return $table;
    }



    /**
     * 获取数据表的字段
     * @param null $table 数据表名
     * @param bool $hasPri 是否返回主键名
     *
     * @return array|mixed
     */
    public static function getTableColumns($table=null, $hasPri=false) {
        if(empty($table)) $table = self::getTableName();
        if(is_null(static::$tableColumns) || !isset(static::$tableColumns[$table])) {
            $_conn = LkkCmponent::SyncDbMaster('');
            $res = $_conn->fetchAll(" SHOW FULL COLUMNS FROM {$table} ");
            if($res) {
                $pri = null;
                $fields = [];
                foreach ($res as $v) {
                    $field = trim($v['Field']);
                    array_push($fields, $field);

                    if(trim($v['Key']) =='PRI') {
                        $pri = $field;
                    }
                }

                static::$tableColumns[$table] = [
                    'all' => $fields,
                    'pri'    => $pri,
                ];
            }
        }

        $res = $hasPri ? static::$tableColumns[$table] : static::$tableColumns[$table]['all'];
        getLogger()->info('getTableColumns', ['$table'=>$table, '$res'=>$res, 'self::$tableColumns'=>static::$tableColumns]);

        return $res;
    }


    /**
     * 过滤表字段数据(排除非表字段的元素)
     * @param array $data
     * @param null  $table
     *
     * @return array|bool
     */
    public static function filterColumnsData(array $data, $table=null) {
        getLogger()->info('filterColumnsData-start', ['$table'=>$table, '$data'=>$data]);
        if(!is_array($data) || empty($data)) return false;
        $columns = self::getTableColumns($table);
        getLogger()->info('filterColumnsData-inner', ['$table'=>$table, '$columns'=>$columns]);
        foreach ($data as $k=>$v) {
            if(!in_array($k, $columns)) unset($data[$k]);
        }
        getLogger()->info('filterColumnsData-end', ['$table'=>$table, '$data'=>$data]);
        return $data;
    }


    /**
     * 字段名分析
     * @param $key
     *
     * @return mixed
     */
    protected static function parseKey($key) {
        return $key;
    }


    /**
     * value分析(where条件中的value)
     * @param $value
     *
     * @return array|string
     */
    protected static function parseValue($value) {
        if(is_numeric($value)) {
            $value  = trim($value);
        }elseif (is_null($value)) {
            $value = 'null';
        }elseif (is_bool($value)) {
            $value = $value ? '1' : '0';
        }elseif (is_array($value)) {
            $value = array_map('self::parseValue', $value);
        }elseif (is_string($value)) {
            $value = trim($value, '\'" ');
            if($value=='') $value = '\'\'';
        }else{
            $value = strval($value);
        }

        return $value;
    }


    /**
     * 分析insert/update的值
     * @param $value
     *
     * @return array|string
     */
    public static function parseInsertValue($value) {
        if (is_array($value)) {
            return array_map('self::parseInsertValue', $value);
        }elseif (is_null($value)) {
            return 'null';
        }elseif (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if(is_string($value) || is_numeric($value)) {
            $value = trim($value);
        }else{
            $value = strval($value);
        }

        if(is_numeric($value)) {
            return $value;
        }elseif ($value=='') {
            return '\'\'';
        }

        //字符串处理
        if(substr_count($value, "'")==0) {
            $value = "'" . $value . "'";
        }else{
            $value = "'" . str_replace("'", "\'", $value) . "'";
        }

        return $value;
    }


    /**
     * 获取表达式全部操作符
     * @return array
     */
    public static function getExpressionOperators() {
        return array_unique(array_merge(array_keys(self::$exp) , array_values(self::$exp)));
    }


    /**
     * 获取SQL的操作符
     * @param $str
     *
     * @return bool
     */
    protected static function getOperator($str) {
        static $_operators;
        if(strlen(trim($str))==1) return false;

        if (empty($_operators)) {
            $_les = (self::$_like_escape_str !== '')
                ? '\s+'.preg_quote(trim(sprintf(self::$_like_escape_str, self::$_like_escape_chr)), '/')
                : '';
            $_operators = [
                '\s*(?:<|>|!)?=\s*',             // =, <=, >=, !=
                '\s*<>?\s*',                     // <, <>
                '\s*>\s*',                       // >
                '\s+IS NULL',                    // IS NULL
                '\s+IS NOT NULL',                // IS NOT NULL
                '\s+EXISTS\s*\([^\)]+\)',        // EXISTS(sql)
                '\s+NOT EXISTS\s*\([^\)]+\)',    // NOT EXISTS(sql)
                '\s+BETWEEN\s+',                 // BETWEEN value AND value
                '\s+IN\s*\([^\)]+\)',            // IN(list)
                '\s+NOT IN\s*\([^\)]+\)',        // NOT IN (list)
                '\s+LIKE\s+\S.*('.$_les.')?',    // LIKE 'expr'[ ESCAPE '%s']
                '\s+NOT LIKE\s+\S.*('.$_les.')?' // NOT LIKE 'expr'[ ESCAPE '%s']
            ];

        }

        return preg_match('/'.implode('|', $_operators).'/i', $str, $match) ? $match[0] : false;
    }


    /**
     * 抛出SQL异常
     * @param mixed $where
     *
     * @throws \Exception
     */
    protected static function thorwSQLException($where=null) {
        //条件表达式不合法
        throw new \Exception('Sql expression is not valid:'. var_export($where, true));
    }



    /**
     * 解析where为PDO格式
     * @param array $where
     *
     * @return array|string
     */
    public static function parseWhere2PDO($where=[]) {
        $where = self::parseWhere($where, true);

        if(isset($where['conditions'])) {
            //主要是将'a=?0 AND b=?1' PHQL形式
            //转换为 'a=? AND b=?' PDO形式
            $where['conditions'] = preg_replace('/\?\d+/','?', $where['conditions']);
        }

        return $where;
    }


    /**
     * where分析
     * @param mixed $where 条件(字符串或数组)
     * @param bool  $bind 是否返回参数绑定形式
     *
     * @return mixed|array|string
     */
    public static function parseWhere($where, $bind=true) {
        $res = $parseArr = [];
        if(is_string($where)) { //字符串条件,参考CI
            if(!$bind) return $where;

            $res = self::parseWhereString($where);
            $res['conditions'] = preg_replace('/ +/', ' ', $res['conditions']);
        }elseif(is_array($where)) { //数组条件,类似YII2
            $whereStr = self::buildWhereArray($where);
            $res = $bind ? self::parseWhere($whereStr, true) : $whereStr;
        }

        return $res;
    }


    /**
     * 解析where字符串为参数绑定模式
     * @param string $where 条件字符串
     * @param array  $lastRes 上次分析结果
     *
     * @return array
     */
    public static function parseWhereString($where='', &$lastRes=[]) {
        $res = [
            'conditions' => '',
            'bind' => [],
        ];

        if(empty($lastRes)) $lastRes = $res;

        if(empty($where)) {
            $res['conditions'] = ' 1=1 ';
            return $res;
        }

        $where = str_replace(['(',')'], [' ( ',' ) '], $where);
        $where = str_replace(PHP_EOL, ' ', $where); //换行符替换为空格

        //分解多层括号
        if(substr_count($where,')')>=1) {
            //多条件里面的括号
            $splitWheres = self::parseBrackets($where);
            if(count($splitWheres)==1 && preg_match('/(?:\()(.*)(?:\))/i', $splitWheres[0], $match)) { //匹配最外层括号()
                $subRes = self::parseWhereString($match[1], $lastRes);
                $res['conditions'] = $lastRes['conditions'] . ' ( ' . $subRes['conditions'] . ' ) ';
                $res['bind'] = $subRes['bind'];

                return $res;
            }

            foreach ($splitWheres as $splitWhere) {
                if(empty($splitWhere)) continue;
                if(strlen($splitWhere)<=5) {
                    $res['conditions'] .= $splitWhere;
                }else{
                    $subRes = self::parseWhereString($splitWhere, $lastRes);
                    $res['conditions'] .= $subRes['conditions'];
                    $res['bind'] = $subRes['bind'];
                }
            }

            $res['conditions'] = $lastRes['conditions'] . $res['conditions'];
            return $res;
        }

        $whereStr = '';
        $bindArr = [];
        $bindNum = count($lastRes['bind']);

        $conditions = preg_split(
            '/((?:^|\s+)AND\s+|(?:^|\s+)OR\s+)/i',
            $where,
            -1,
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
        );

        for ($ci = 0, $cc = count($conditions); $ci < $cc; $ci++) {
            $op = self::getOperator($conditions[$ci]);
            if($op===false) { //无操作符
                if(strpos($conditions[$ci], ')') &&
                    preg_match('/^(((?!\)).)*)(.*)$/i', $conditions[$ci], $sub_matches) &&
                    !in_array(trim($sub_matches[1]), self::$exp)) { //between 后面的)
                    array_push($bindArr, trim($sub_matches[1], '\'" '));
                    //$bindArr[$bindNum] = trim($sub_matches[1], '\'" ');
                    $sub_matches[1] = '?'.$bindNum;
                    $bindNum++;
                    unset($sub_matches[0]);
                    $whereStr .= implode(' ', $sub_matches);
                }else{
                    $whereStr .= $conditions[$ci];
                }

                continue;
            }

            preg_match('/^(\(?)(.*)('.preg_quote($op, '/').')\s*(.*(?<!\)))?(\)?)$/i', $conditions[$ci], $matches);
            if(strlen(trim($matches[3]))<=2) { //1~2个字符的操作符
                if(strpos($matches[4], ')')) {
                    preg_match('/^(((?!\)).)*)(.*)$/i', $matches[4], $sub_matches);
                    array_push($bindArr, trim($sub_matches[1], '\'" '));
                    //$bindArr[$bindNum] = trim($sub_matches[1], '\'" ');
                    $matches[4] = '?'.$bindNum;
                    $bindNum++;
                    $matches[5] = $sub_matches[3];
                    unset($matches[0]);
                    $whereStr .= implode(' ', $matches);
                }else{
                    //array_push($bindArr, trim($matches[4], '\'" '));
                    $bindArr[$bindNum] = trim($matches[4], '\'" ');
                    $matches[4] = '?'.$bindNum;
                    $bindNum++;
                    unset($matches[0]);
                    $whereStr .= implode(' ', $matches);
                }
            }elseif(stripos($matches[3], 'like')) { // [NOT]LIKE条件
                preg_match('/(not)?\s+(like)(((?!\)).)*)(.*)$/i', $matches[3], $sub_matches);
                array_push($bindArr, trim($sub_matches[3], '\'" '));
                //$bindArr[$bindNum] = trim($sub_matches[3], '\'" ');
                $sub_matches[3] = '?'.$bindNum;
                $bindNum++;
                unset($sub_matches[0], $sub_matches[4], $matches[0]);
                $matches[3] = implode(' ', $sub_matches);
                $whereStr .= implode(' ', $matches);
            }elseif(stripos($matches[3], 'in')) { // [NOT]IN条件
                preg_match('/(not)?\s+(in\s+\()(((?!\)).)*)(.*)$/i', $matches[3], $sub_matches);
                $tmpArr = explode(',', $sub_matches[3]);
                $fillArr = [];

                foreach ($tmpArr as $v) {
                    array_push($bindArr, trim($v, '\'" '));
                    //$bindArr[$bindNum] = trim($v, '\'" ');
                    $fillArr[] = '?'.$bindNum;
                    $bindNum++;
                }

                $sub_matches[3] = implode(' , ', $fillArr);
                unset($sub_matches[0], $matches[0]);
                $matches[3] = implode(' ', $sub_matches);
                $whereStr .= implode(' ', $matches);
            }elseif(stripos($matches[3], 'between')) { // BETWEEN 条件
                array_push($bindArr, trim($matches[4], '\'" '));
                //$bindArr[$bindNum] = trim($matches[4], '\'" ');
                $matches[4] = '?'.$bindNum;
                $bindNum++;
                unset($matches[0]);
                $whereStr .= implode(' ', $matches);
            }else{
                $whereStr .= $conditions[$ci];
            }

        }

        foreach ($bindArr as $item) {
            array_push($lastRes['bind'], $item);
        }

        $res = [
            'conditions' => $lastRes['conditions'] . $whereStr,
            'bind' => $lastRes['bind'],
        ];

        return $res;
    }


    /**
     * 解析括号
     * @param string $str WHERE条件
     *
     * @return array
     */
    public static function parseBrackets($str='') {
        $splitWhere = [];
        preg_match_all('/\(([^()]|\([^()]*\))*\)/i', $str, $matchs);
        if(!empty($matchs[0])) {
            $lastEnd = 0;
            foreach ($matchs[0] as $k=>$match) {
                $pos = strpos($str, $match);
                $len = strlen($match);

                $prevStr = trim(substr($str, $lastEnd, ($pos-$lastEnd)));
                $currStr = trim($match);
                if($prevStr) array_push($splitWhere, $prevStr);
                if($currStr) array_push($splitWhere, $currStr);
                $lastEnd = $pos + $len;
            }

            //末尾
            $lastStr = trim(substr($str, $lastEnd, (strlen($str)-$lastEnd)));
            if($lastStr) array_push($splitWhere, $lastStr);
        }

        return $splitWhere;
    }




    /**
     * 解析数组条件
     * @param array $where
     *
     * @return string
     */
    protected static function buildWhereArray(array $where=[]) {
        $whereStr = '';
        if(empty($where)) return $whereStr;

        $allOperators = self::getExpressionOperators();
        $logicOperatorStr = ',AND,XOR,OR';
        $firstItem = current($where);
        $logicOperate = ' AND '; // 默认进行 AND 运算
        if(is_string($firstItem)) {
            $firstItem = strtoupper(trim($firstItem));
            if(stripos($logicOperatorStr, $firstItem)!==false) { // 定义逻辑运算规则 例如 OR XOR AND
                $logicOperate = ' ' . $firstItem . ' ';
                array_shift($where);
            }
        }

        if(is_string($firstItem) && (in_array($firstItem, $allOperators) || preg_grep("@$firstItem@i", $allOperators))) {
            $where = [$where];
        }

        $whereNum = count($where);
        if($whereNum==0) {
            self::thorwSQLException($where);
        }

        $childConditions = [];
        foreach ($where as $key => $val) {
            $str = '';
            if (is_string($key) && is_scalar($val)) { //常规键值对,单值
                $nullExp = strtolower(strval($val));
                if(stripos($nullExp, 'null')!==false && preg_grep("/$nullExp/i", $allOperators)) { //null,如 'name'=>'null'
                    $str = $key . ' ' . self::$exp[$nullExp];
                }else{
                    $str = $key . ' = ' . self::parseValue($val);
                }
            }elseif (is_string($key) && is_array($val)) { //常规键值对,多值
                $str = $key . ' IN (' . implode(', ', $val) . ')';
            }elseif (is_numeric($key) && is_string($val)) { //字符串条件
                $str = $val;
            }elseif (is_numeric($key) && is_array($val)) { //数组表达式
                $childItemNum = count($val);
                if($childItemNum==0) self::thorwSQLException($where);

                $firstKey = current(array_keys($val));
                $firstVal = current($val);
                if(is_string($firstKey)) { //常规键值对
                    if($childItemNum==1) {
                        $str = is_array($firstVal) ? ($firstKey . ' IN (' . implode(', ', $firstVal) . ')') : ($firstKey . ' = ' . self::parseValue($firstVal));
                    }else{
                        $str = self::buildWhereArray($val);
                    }
                }elseif(is_int($firstKey)){
                    $expOperate = is_string($val[0]) ? strtolower(trim($val[0])) : '';
                    if(stripos($logicOperatorStr, $expOperate)!==false || $expOperate=='') { //嵌套条件
                        $str = self::buildWhereArray($val);
                    }elseif (in_array($expOperate, $allOperators) || preg_grep("/$expOperate/i", $allOperators)) {
                        //检查后面参数是否足够
                        if((stripos($expOperate, 'null')!==false || stripos($expOperate, 'exists')!==false) && !isset($val[1])) {
                            self::thorwSQLException($val);
                        }elseif (stripos($expOperate, 'between')!==false && !isset($val[3])) {
                            self::thorwSQLException($val);
                        }elseif (!isset($val[2])) {
                            self::thorwSQLException($val);
                        }

                        if (in_array($expOperate, ['=','<>','!=','>','>=','<','<='])) { //比较运算,如['>=','id',9]
                            $str = $val[1] . ' ' . $expOperate . ' ' . self::parseValue($val[2]);
                        }elseif (preg_match('/^(eq|neq|gt|egt|lt|elt)$/i', $expOperate)) {//比较运算,如['gt','id',9]
                            $str = $val[1] . ' ' . self::$exp[$expOperate] . ' ' . self::parseValue($val[2]);
                        }elseif (preg_match('/^(notlike|like)$/i', $expOperate)) { //模糊查询,如['like','name','%姓名%']
                            $str = $val[1] . ' ' . self::$exp[$expOperate] . ' ' . self::parseValue($val[2]);
                        }elseif (preg_match('/^(notin|not in|in)$/i', $expOperate)) { //枚举范围,如['in','id',[1,2,3] ]
                            if(is_scalar($val[2])) {
                                $val[2] = explode(',', strval($val[2]));
                            }
                            $enumVlus = implode(', ', self::parseValue($val[2]));
                            $str = $val[1] . ' ' . self::$exp[$expOperate] . ' (' . $enumVlus . ')';
                        }elseif (preg_match('/^(notbetween|not between|between)$/i', $expOperate)) { //数值范围,如['between','time', 10, 59]
                            $str = $val[1] . ' ' . self::$exp[$expOperate] . ' ' . self::parseValue($val[2]) . ' AND ' . self::parseValue($val[3]);
                        }elseif (stripos($expOperate, 'null')!==false) { //IS NULL,如['is null', 'name']
                            $str = $val[1] . ' ' . self::$exp[$expOperate];
                        }elseif (stripos($expOperate, 'exists')!==false){ //exists表达式,如['exists', 'SELECT FROM test WHERE id=1']
                            $str = self::$exp[$expOperate] . ' (' . $val[1] . ')';
                        }else{
                            self::thorwSQLException($val);
                        }
                    }else{
                        self::thorwSQLException($val);
                    }
                }else{
                    self::thorwSQLException($where);
                }
            }else{
                self::thorwSQLException($where);
            }

            if(trim($str)=='') continue;
            $childConditions[] = $whereNum==1 ? $str : ('( ' . $str . ' )');
        }

        $whereStr .= count($childConditions)<=1 ? implode(' ', $childConditions) : implode($logicOperate, $childConditions);

        return $whereStr;
    }



    /**
     * where子单元分析
     * @param $key
     * @param $val
     *
     * @return string
     */
    protected static function parseWhereItem($key, $val) {
        $whereStr = '';
        if (is_array($val)) {
            if (is_string($val[0])) {
                $exp = strtolower($val[0]);
                if (preg_match('/^(eq|neq|gt|egt|lt|elt)$/i', $exp)) {
                    // 比较运算
                    $whereStr .= $key . ' ' . self::$exp[$exp] . ' ' . self::parseValue($val[1]);
                } elseif (preg_match('/^(notlike|like)$/i', $exp)) {
                    // 模糊查找
                    if (is_array($val[1])) {
                        $likeLogic = isset($val[2]) ? strtoupper($val[2]) : 'OR';
                        if (in_array($likeLogic, ['AND', 'OR', 'XOR'])) {
                            $like = [];
                            foreach ($val[1] as $item) {
                                $like[] = $key . ' ' . self::$exp[$exp] . ' ' . self::parseValue($item);
                            }
                            $whereStr .= '(' . implode(' ' . $likeLogic . ' ', $like) . ')';
                        }
                    } else {
                        $whereStr .= $key . ' ' . self::$exp[$exp] . ' ' . self::parseValue($val[1]);
                    }
                } elseif ('exp' == $exp) {
                    // 使用表达式
                    $whereStr .= $key . ' ' . $val[1];
                } elseif (preg_match('/^(notin|not in|in)$/i', $exp)) {
                    // IN 运算
                    if (isset($val[2]) && 'exp' == $val[2]) {
                        $whereStr .= $key . ' ' . self::$exp[$exp] . ' ' . $val[1];
                    } else {
                        if (is_string($val[1])) {
                            $val[1] = explode(',', $val[1]);
                        }
                        $zone = implode(',', self::parseValue($val[1]));
                        $whereStr .= $key . ' ' . self::$exp[$exp] . ' (' . $zone . ')';
                    }
                } elseif (preg_match('/^(notbetween|not between|between)$/i', $exp)) {
                    // BETWEEN运算
                    $data = is_string($val[1]) ? explode(',', $val[1]) : $val[1];
                    $whereStr .= $key . ' ' . self::$exp[$exp] . ' ' . self::parseValue($data[0]) . ' AND ' . self::parseValue($data[1]);
                } else {
                    //TODO
                }
            } else {
                $count = count($val);
                $rule  = isset($val[$count - 1]) ? (is_array($val[$count - 1]) ? strtoupper($val[$count - 1][0]) : strtoupper($val[$count - 1])) : '';
                if (in_array($rule, ['AND', 'OR', 'XOR'])) {
                    --$count;
                } else {
                    $rule = 'AND';
                }
                for ($i = 0; $i < $count; $i++) {
                    $data = is_array($val[$i]) ? $val[$i][1] : $val[$i];
                    if ('exp' == strtolower($val[$i][0])) {
                        $whereStr .= $key . ' ' . $data . ' ' . $rule . ' ';
                    } else {
                        $whereStr .= self::parseWhereItem($key, $val[$i]) . ' ' . $rule . ' ';
                    }
                }
                $whereStr = '( ' . substr($whereStr, 0, -4) . ' )';
            }
        } else {
            $whereStr .= $key . ' = ' . self::parseValue($val);
        }
        return $whereStr;
    }



    /**
     * 插入单条记录(同步)
     * @param array  $data 数据(键值对)
     * @param string $table 表名
     *
     * @return bool
     */
    public static function addData(array $data =[], $table='') {
        if(!is_array($data) || empty($data) ) return false;
        if(empty($table)) $table = self::getTableName();

        $data = self::filterColumnsData($data, $table);
        if(empty($data)) return false;

        $_conn = LkkCmponent::SyncDbMaster('');
        $res = $_conn->insert($table, array_values($data), array_keys($data));
        $lastId = $_conn->lastInsertId();

        if($res && $lastId>0) $res = $lastId;

        return $res;
    }


    /**
     * 插入单条记录(异步)
     * @param array  $data 一维数组
     * @param string $table
     *
     * @return bool|int
     */
    public static function addDataAsync(array $data =[], $table='') {
        if(!is_array($data) || empty($data) ) return false;
        if(empty($table)) $table = self::getTableName();

        $data = self::filterColumnsData($data, $table);
        if(empty($data)) return false;

        //检查表字段
        $tabFields = self::getTableColumns($table);
        $insertFields = array_keys($data);
        $checkDiff = array_diff($insertFields, $tabFields);
        if(!empty($checkDiff)) return false;

        $rowsString = sprintf('`%s`', implode('`,`', $insertFields));
        $insertString = "INSERT INTO `%s` (%s) VALUE %s";

        $valueArr = array_values($data);
        foreach ($valueArr as &$item) {
            $item = self::parseInsertValue($item);
        }
        $valueString = '(' . implode(', ', $valueArr) . ')';
        $query = sprintf($insertString,
            $table,
            $rowsString,
            $valueString
        );

        $asyncMysql = SwooleServer::getPoolManager()->get('mysql_master')->pop();
        $res = yield $asyncMysql->execute($query, true);
        return ($res && $res['code']==0) ? $res['insert_id'] : false;
    }


    /**
     * 插入多条数据(同步)
     * @param array  $data 数据(二维数组)
     * @param string $table 表名
     * @param bool   $ignore 是否忽略重复记录
     * @param int    $sliceNum 每批数量
     *
     * @return bool|int
     */
    public static function addMultiData(array $data=[], $table='', $ignore=false, $sliceNum=25) {
        if(!is_array($data) || empty($data) ) return false;
        if(empty($table)) $table = self::getTableName();

        //检查表字段
        $tabFields = self::getTableColumns($table);
        $insertFields = array_keys(current($data));
        $checkDiff = array_diff($insertFields, $tabFields);
        if(!empty($checkDiff)) return false;

        $_conn = LkkCmponent::SyncDbMaster('');
        $rowsString = sprintf('`%s`', implode('`,`', $insertFields));
        $fieldCount = count($data[0]);
        $affectedRows = 0;
        if($ignore){
            $insertString = "INSERT IGNORE INTO `%s` (%s) VALUES %s";
        } else {
            $insertString = "INSERT INTO `%s` (%s) VALUES %s";
        }

        for ($i = 0, $total = count($data); $i < $total; $i += $sliceNum){
            $batchData = array_slice($data, $i, $sliceNum);
            $valueCount = count($batchData);

            $placeholders = [];
            for ($j = 0; $j < $valueCount; $j++) {
                $placeholders[] = '(' . rtrim(str_repeat('?,', $fieldCount), ',') . ')';
            }
            $bindString = implode(',', $placeholders);

            $valueList = [];
            foreach ($batchData as $value) {
                if (is_array($value)) {
                    foreach ($value as $v) {
                        $valueList[] = $v;
                    }
                }else{
                    $valueList[] = $value;
                }
            }
            $valuesFlattened = $valueList;

            $query = sprintf($insertString,
                $table,
                $rowsString,
                $bindString
            );

            $_conn->execute($query, $valuesFlattened);
            $affectedRows += $_conn->affectedRows();
        }//end for

        return $affectedRows;
    }


    /**
     * 插入多条数据(异步)
     * @param array  $data 数据(二维数组)
     * @param string $table 表名
     * @param bool   $ignore 是否忽略重复记录
     * @param int    $sliceNum 每批数量
     *
     * @return bool|int
     */
    public static function addMultiDataAsync(array $data=[], $table='', $ignore=false, $sliceNum=25) {
        if(!is_array($data) || empty($data) ) return false;
        if(empty($table)) $table = self::getTableName();

        //检查表字段
        $tabFields = self::getTableColumns($table);
        $insertFields = array_keys(current($data));
        $checkDiff = array_diff($insertFields, $tabFields);
        if(!empty($checkDiff)) return false;

        $affectedRows = 0;
        $rowsString = sprintf('`%s`', implode('`,`', $insertFields));
        if($ignore){
            $insertString = "INSERT IGNORE INTO `%s` (%s) VALUES %s";
        } else {
            $insertString = "INSERT INTO `%s` (%s) VALUES %s";
        }

        //数组分段
        $slices = array_chunk($data, $sliceNum, true);
        unset($data);
        $asyncMysql = SwooleServer::getPoolManager()->get('mysql_master')->pop();
        foreach ($slices as $slice) {
            $mulVals = [];
            foreach ($slice as $row) {
                $valueArr = array_values($row);
                foreach ($valueArr as &$item) {
                    $item = self::parseInsertValue($item);
                }
                $mulVals[] = '(' . implode(', ', $valueArr) . ')';
            }
            $valuesString = implode(', ', $mulVals);

            $query = sprintf($insertString,
                $table,
                $rowsString,
                $valuesString
            );

            $insertRes = yield $asyncMysql->execute($query, true);
            if($insertRes['code']==0) $affectedRows += $insertRes['affected_rows'];
        }

        return $affectedRows;
    }


    /**
     * 更新数据(同步)
     * @param array  $data
     * @param string $where
     * @param null   $table
     *
     * @return bool
     * @throws \Exception
     */
    public static function upData(array $data=[], $where='', $table=null) {
        if(!is_array($data) || empty($data) ) return false;
        if(empty($table)) $table = self::getTableName();
        $data = self::filterColumnsData($data, $table);
        if(empty($data)) return false;
        $where = self::parseWhere2PDO($where);

        $_conn = LkkCmponent::SyncDbMaster('');
        $res = $_conn->update($table, array_keys($data), array_values($data), $where);
        $err = $_conn->getErrorInfo();

        if(!$res && is_array($err) && $err[0]!='00000') {
            throw new \Exception(json_encode($err));
        }

        return $res;
    }


    /**
     * 更新数据(异步)
     * @param array  $data
     * @param string $where
     * @param null   $table
     *
     * @return bool
     * @throws \Exception
     */
    public static function upDataAsync(array $data=[], $where='', $table=null) {
        if(!is_array($data) || empty($data) ) return false;
        if(empty($table)) $table = self::getTableName();

        $data = self::filterColumnsData($data, $table);
        if(empty($data) || empty($where)) return false;
        $whereString = self::parseWhere($where, false);

        $updateString = "UPDATE `%s` SET %s WHERE %s";

        $valusArr = [];
        foreach ($data as $key=>$vue) {
            $vue = self::parseInsertValue($vue);
            $valusArr[] = "`{$key}` = ".$vue;
        }
        $valuesString = implode(', ', $valusArr);

        $query = sprintf($updateString,
            $table,
            $valuesString,
            $whereString
        );

        $asyncMysql = SwooleServer::getPoolManager()->get('mysql_master')->pop();
        $res = yield $asyncMysql->execute($query, true);

        if($res['code']!=0 && isset($res['errno'])) {
            throw new \Exception($res['errno']);
        }

        return ($res && $res['code']==0) ? $res['affected_rows'] : false;
    }



    /**
     * 删除数据(同步)
     * @param $where
     * @param null $table
     * @return bool
     */
    public static function delData($where, $table=null) {
        if(empty($table)) $table = self::getTableName();
        $_conn = LkkCmponent::SyncDbMaster('');
        $where = self::parseWhere2PDO($where);
        return $_conn->delete($table, $where['conditions'], $where['bind']);
    }


    /**
     * 删除记录(异步)
     * @param      $where
     * @param null $table
     *
     * @return bool
     */
    public static function delDataAsync($where, $table=null) {
        if(empty($where)) return false;
        if(empty($table)) $table = self::getTableName();

        $whereString = self::parseWhere($where, false);
        $deleteString = "DELETE FROM `%s` WHERE %s";

        $query = sprintf($deleteString,
            $table,
            $whereString
        );

        $asyncMysql = SwooleServer::getPoolManager()->get('mysql_master')->pop();
        $res = yield $asyncMysql->execute($query, true);
        return ($res && $res['code']==0) ? $res['affected_rows'] : false;
    }


    /**
     * 获取当前表分页对象(同步)
     * @param string $columns 字段
     * @param string $where 条件
     * @param string $order 排序
     * @param int $limit 每页数量
     * @param int $page 当前页码
     * @return PaginatorQueryBuilder
     */
    public static function getPaginator($columns='*', $where='', $order='', $limit=10, $page=1) {
        $table = self::getTableName();
        $tableColumns = self::getTableColumns($table, true);
        if(empty($columns)) $columns = implode(',', $tableColumns['all']);
        if(empty($where)) $where = '1=1';
        $where = self::parseWhere($where, true);
        if(empty($order)) $order = (empty($tableColumns['pri']) ? $tableColumns['all'][0] : $tableColumns['pri']);

        $params = [
            'models'     => get_called_class(),
            'columns'    => array_filter(explode(',', $columns), 'trim'),
            'conditions' => [array_values($where)],
            'order'      => empty($order) ? null : $order,
        ];

        $builder = new QueryBuilder($params);

        return new PaginatorQueryBuilder(
            [
                "builder" => $builder,
                "limit"   => $limit,
                "page"    => $page
            ]
        );
    }


    /**
     * 获取当前表分页对象(异步)
     * @param string $columns
     * @param string $where
     * @param string $order
     * @param int    $limit
     * @param int    $page
     *
     * @return PaginatorAsyncMysql
     */
    public static function getPaginatorAsync($columns='*', $where='', $order='', $limit=10, $page=1) {
        $table = self::getTableName();
        $tableColumns = self::getTableColumns($table, true);
        if(empty($columns)) $columns = implode(',', $tableColumns['all']);
        if(empty($where)) $where = '1=1';
        $where = self::parseWhere($where, false);
        if(empty($order)) $order = (empty($tableColumns['pri']) ? $tableColumns['all'][0] : $tableColumns['pri']);

        $params = [
            'models'     => get_called_class(),
            'columns'    => array_filter(explode(',', $columns), 'trim'),
            'conditions' => [array_values($where)],
            'order'      => empty($order) ? null : $order,
        ];

        $builder = new QueryBuilder($params);

        return new PaginatorAsyncMysql(
            [
                "builder" => $builder,
                "limit"   => $limit,
                "page"    => $page
            ]
        );
    }



    /**
     * 获取一条记录(同步)
     * @param mixed  $where 条件(字符串或数组)
     * @param string $field 字段
     * @param string $order 排序
     * @return array|\Phalcon\Mvc\Model|\Phalcon\Mvc\ModelInterface
     */
    public static function getRow($where='', $field='*', $order='') {
        if(empty($where)) $where ='1=1';
        $where = self::parseWhere($where, true);

        if(trim($field)=='*' || trim($field)=='') {
            if(!empty($order)) $where['order'] = $order;
            $res = self::findFirst($where);
        }else{
            $params = [
                'models'     => get_called_class(),
                'columns'    => array_filter(explode(',', $field), 'trim'),
                'conditions' => [array_values($where)],
                'order'      => empty($order) ? null : $order,
                'limit'      => 1,
                'offset'     => 0,
            ];
            $queryBuilder = new QueryBuilder($params);
            $res = $queryBuilder->getQuery()->getSingleResult();
        }

        return $res;
    }


    /**
     * 获取一条记录(异步)
     * @param string $where
     * @param string $field
     * @param string $order
     * @param string $table
     *
     * @return bool|array
     */
    public static function getRowAsync($where='', $field='*', $order='', $table='') {
        if(empty($table)) $table = self::getTableName();
        if(empty($where)) $where ='1=1';
        $whereString = self::parseWhere($where, false);

        $selectString = "SELECT %s FROM `%s` WHERE %s";
        $query = sprintf($selectString,
            $field,
            $table,
            $whereString
        );

        if($order) $query .= " ORDER BY {$order}";
        $query .= " LIMIT 1";

        $asyncMysql = SwooleServer::getPoolManager()->get('mysql_master')->pop();
        $res = yield $asyncMysql->execute($query, true);
        return ($res && $res['code']==0) ? $res['data'] : false;
    }



    /**
     * 获取记录列表
     * @param mixed  $where 条件(字符串或数组)
     * @param string $field 字段,注意不能*不能和某字段一起,如'*,id AS pid'(这是错误的);多个字段必须写具体,如'id,name,type'
     * @param string $order 排序
     * @param int    $limit 数量
     *
     * @return array|Model
     */
    public static function getList($where='', $field='*', $order='', $limit=0) {
        if(empty($where)) $where ='1=1';
        $where = self::parseWhere($where, true);

        if(trim($field)=='*' || trim($field)=='') {
            if(!empty($order)) $where['order'] = $order;
            if($limit>0) $where['limit'] = $limit;
            $res = self::find($where);
        }else{
            $params = [
                'models'     => get_called_class(),
                'columns'    => array_filter(explode(',', $field), 'trim'),
                'conditions' => [array_values($where)],
                'order'      => empty($order) ? null : $order,
                'limit'      => ($limit>0 ? $limit : null),
            ];

            $queryBuilder = new QueryBuilder($params);
            $res = $queryBuilder->getQuery()->execute();
        }

        return $res;
    }


    /**
     * 获取记录列表(异步)
     * @param string $where
     * @param string $field
     * @param string $order
     * @param int    $limit
     * @param string $table
     *
     * @return bool
     */
    public static function getListAsync($where='', $field='*', $order='', $limit=0, $table='') {
        if(empty($table)) $table = self::getTableName();
        if(empty($where)) $where ='1=1';
        $whereString = self::parseWhere($where, false);

        $selectString = "SELECT %s FROM `%s` WHERE %s";
        $query = sprintf($selectString,
            $field,
            $table,
            $whereString
        );

        if($order) $query .= " ORDER BY {$order}";
        if($limit) $query .= " LIMIT {$limit}";

        $asyncMysql = SwooleServer::getPoolManager()->get('mysql_master')->pop();
        $res = yield $asyncMysql->execute($query, false);
        return ($res && $res['code']==0) ? $res['data'] : false;
    }


    /**
     * 获取count统计
     * @param $where
     * @return int
     */
    public static function getCount($where) {
        $where = self::parseWhere($where, true);
        $params = array(
            'models'     => get_called_class(),
            'columns'    => 'count(1) AS total',
            'conditions' => [array_values($where)],
            'limit'      => 1,
        );
        $queryBuilder = new QueryBuilder($params);
        $res = $queryBuilder->getQuery()->getSingleResult();

        return (int)$res->total;
    }


    /**
     * 统计记录(异步)
     * @param        $where
     * @param string $table
     *
     * @return bool|int
     */
    public static function getCountAsync($where, $table='') {
        if(empty($table)) $table = self::getTableName();
        if(empty($where)) $where ='1=1';
        $whereString = self::parseWhere($where, false);

        $countString = "SELECT COUNT(1) AS total FROM `%s` WHERE %s";
        $query = sprintf($countString,
            $table,
            $whereString
        );

        $asyncMysql = SwooleServer::getPoolManager()->get('mysql_master')->pop();
        $res = yield $asyncMysql->execute($query, true);
        return ($res && $res['code']==0) ? ($res['data']['total']??0) : false;
    }


    /**
     * 生成分布式唯一guid
     * @return int|string 最大19位正整数(注意不能intval取整,会溢出)
     */
    public static function makeGuid() {
        //获取服务器网卡
        static $macAddr;
        if(is_null($macAddr)) {
            $macAddr = LkkMacAddress::getMacAddress();
        }

        //8位随机数
        $randNum = sprintf("%08s", mt_rand(0, 999999999));

        //服务器相关
        $serverStr = $macAddr . SwooleServer::getLocalIp() . self::getTableName() . getSiteUrl();

        //hash串
        $hashStr = md5(uniqid('',true)) . microtime(true) . $serverStr . $randNum . str_shuffle($serverStr);

        //生成11位Int
        $guid = EncryptHelper::murmurhash3_int($hashStr, 31, true);

        //转换为19位
        $guid .= $randNum;

        return $guid;
    }


    /**
     * 单个row对象转为array
     * left join连表查询的结果toArray()会[join result is grouped by model/table names],故用此方法
     * @param $obj
     * @return array
     */
    public static function rowToArray($obj) {
        if(!is_object($obj) && !method_exists($obj, 'toArray')) return [];
        $arr = method_exists($obj, 'toArray') ? $obj->toArray() : (array)$obj;
        $new = [];

        foreach ($arr as $k=>$item) {
            if(is_object($item)) {
                unset($arr[$k]);
                $tmp = self::rowToArray($item);
                if(count($tmp)==1 && isset($tmp[0])) $tmp = current($tmp);

                $new = array_merge($new, $tmp);
            }
        }

        return array_merge($new, $arr);
    }


    /**
     * 单个row对象转为stdClass obj
     * @param $obj
     * @return array|object
     */
    public static function rowToObject($obj) {
        $arr = self::rowToArray($obj);
        $obj = ArrayHelper::arrayToObject($arr);
        return $obj;
    }




}
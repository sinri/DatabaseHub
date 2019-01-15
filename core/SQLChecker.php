<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018-12-10
 * Time: 15:57
 */

namespace sinri\databasehub\core;


use PhpMyAdmin\SqlParser\Components\Limit;
use PhpMyAdmin\SqlParser\Parser;
use PhpMyAdmin\SqlParser\Statement;
use PhpMyAdmin\SqlParser\Utils\Query;
use PHPSQLParser\PHPSQLParser;

class SQLChecker
{

    const QUERY_TYPE_SELECT = "SELECT";
    const QUERY_TYPE_ALTER = 'ALTER';
    const QUERY_TYPE_CREATE = 'CREATE';
    const QUERY_TYPE_ANALYZE = 'ANALYZE';
    const QUERY_TYPE_CHECK = 'CHECK';
    const QUERY_TYPE_CHECKSUM = 'CHECKSUM';
    const QUERY_TYPE_OPTIMIZE = 'OPTIMIZE';
    const QUERY_TYPE_REPAIR = 'REPAIR';
    const QUERY_TYPE_CALL = 'CALL';
    const QUERY_TYPE_DELETE = 'DELETE';
    const QUERY_TYPE_DROP = 'DROP';
    const QUERY_TYPE_EXPLAIN = 'EXPLAIN';
    const QUERY_TYPE_INSERT = 'INSERT';
    const QUERY_TYPE_LOAD = 'LOAD';
    const QUERY_TYPE_REPLACE = 'REPLACE';
    const QUERY_TYPE_SHOW = 'SHOW';
    const QUERY_TYPE_UPDATE = 'UPDATE';
    const QUERY_TYPE_SET = 'SET';

    /// SPLITTER

    /**
     * @param String $query
     * @return String[]
     */
    public static function split($query)
    {
        $parser = new Parser($query);
        // $flags = Query::getFlags($parser->statements[0]);
        // return $flags;
        $result = array();
        if (is_array($parser->statements)) {
            foreach ($parser->statements as $statement) {
                $single_sql = self::dealStatement($statement);
                $result[] = $single_sql;
            }
        } else {
            $single_sql = self::dealStatement($parser->statements);
            $result[] = $single_sql;
        }
        return $result;
    }

    /**
     * @param Statement|mixed $statement
     * @return String
     */
    private static function dealStatement($statement)
    {
        $sql = $statement->build();
        return $sql;
    }

    /**
     * @param String $query
     * @return String
     */
    public static function getTypeOfSingleSql($query)
    {
        $parser = new Parser($query);
        $flags = Query::getFlags($parser->statements[0]);
        return $flags['querytype'];
    }

    /**
     * @param string $sql
     * @param int $max_limit
     * @return string The original sql if well limited, or the modified sql.
     * @throws \Exception
     */
    public static function processSqlForQuickQuery($sql, $max_limit = 512)
    {
        $split_result = self::split($sql);
        if (empty($split_result)) {
            throw new \Exception("The SQL seems have syntax problem.");
        }
        $sql = $split_result[0];
        $parser = new Parser($sql);
        if (
            !isset($parser->statements[0]->limit)
            || !$parser->statements[0]->limit
            || $parser->statements[0]->limit->rowCount > $max_limit
        ) {
            $parser->statements[0]->limit = new Limit(30, 0);
            $statement = $parser->statements[0];
            $sql2 = $statement->build();
            return $sql2;
        }
        return $sql;
    }

    /// CHECKER

    public static function validateSqlGrammar($sql, &$error = null)
    {
        try {
            // Try to fix 'cannot calculate position' issue, turn it off
            $calcPositions = false;
            // use @ to avoid lots of Notices and Warnings as Issue https://github.com/greenlion/PHP-SQL-Parser/issues/279
            // Now use my own repo and a customized version 4.1.2.2, fixed the issue on top
            $parser = new PHPSQLParser($sql, $calcPositions);
            return ($parser->parsed != false);
        } catch (\Exception $e) {
            $error = $e->getMessage();
            return false;
        }
    }

    public static function checkIfAvailableCallStatement($sql)
    {
        $x_sql = trim($sql);
        $x_sql = preg_replace('/^--[\s\r\n]?.*$/', '', $x_sql);
        $x_sql = preg_replace('/[\r\n]+/', '', $x_sql);
        if (stripos($x_sql, 'call ') === 0) {
            //这货是CALL
            if (preg_match('/^call +[A-Za-z0-9_]+\((((\'.+\')|([0-9.]+))?(,((\'.+\')|([0-9.]+)))*)\);?$/', $x_sql)) {
                //符合语法
                return true;
            }
        }
        return false;
    }

    public static function checkIfAvailableTruncateStatement($sql)
    {
        $x_sql = trim($sql);
        //$x_sql=preg_replace('/^--[\s\r\n]?.*$/', '', $x_sql);
        if (preg_match(
            '/^(truncate ([A-Za-z0-9_]+\.)[A-Za-z0-9_]+)(\s*;\s*(truncate ([A-Za-z0-9_]+\.)[A-Za-z0-9_]+)?)*$/',
            $x_sql
        )) {
            // 这货是truncate
            return true;
        }
        return false;
    }

    public static function checkIfAvailableKillStatement($sql)
    {
        $x_sql = trim($sql);
        if (preg_match('/^kill[\s\r\n]+\d+(;[\s\r\n]*(kill[\s\r\n]+\d+)?)*$/', $x_sql)) {
            // 这货是kill
            return true;
        }
        return false;
    }
}
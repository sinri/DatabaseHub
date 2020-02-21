<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018-12-15
 * Time: 12:48
 */
namespace sinri\databasehub\library;

use Exception;
use sinri\databasehub\entity\DatabaseEntity;
use sinri\databasehub\entity\DatabaseWorkerEntity;

class DDCompare
{
    public $showSameCompareResults = true;
    public $showSameLinesInDiffDetails = true;
    public $ignoreTableAutoIncrement = true;
    public $ignoreDefiner = true;
    public $ignoreTableUsingBTree = true;
    public $ignoreTableRowFormat = true;
    //public $ignoreFunctionDefiner = false;
    //public $ignoreProcedureDefiner = false;

    /**
     * @var DatabaseWorkerEntity
     */
    protected $workerEntityA;

    /**
     * @var DatabaseWorkerEntity
     */
    protected $workerEntityB;

    /**
     * @var DatabaseEntity
     */
    protected $databaseA;

    /**
     * @var DatabaseEntity
     */
    protected $databaseB;

    protected $nickNameA;
    protected $nickNameB;

    protected $result;

    /**
     * DDCompare constructor.
     * @param DatabaseEntity $databaseA
     * @param string $nickNameA
     * @param DatabaseEntity $databaseB
     * @param string $nickNameB
     * @throws Exception
     */
    public function __construct($databaseA, $nickNameA, $databaseB, $nickNameB)
    {
        $this->databaseA = $databaseA;
        $this->databaseB = $databaseB;
        $this->nickNameA = $nickNameA;
        $this->nickNameB = $nickNameB;
        $this->workerEntityA = $databaseA->getWorkerEntity();
        $this->workerEntityB = $databaseB->getWorkerEntity();
    }

    /**
     * @param $targetName
     * @param $sqlA
     * @param $sqlB
     * @param int|string $columnIndex
     * @param array $filter_rules
     */
    protected function fetchShowResultAndCompare($targetName, $sqlA, $sqlB, $columnIndex = 1, $filter_rules = [])
    {
        try {
            $strA = $this->workerEntityA->getCol($sqlA, $columnIndex);
        } catch (Exception $exception) {
            $this->result[] = $this->nickNameA."  ".$exception->getMessage();
            $strA = [];
        }
        if (empty($strA)) {
            // does not exist in A
            $this->result[] = "- " . ($this->nickNameA . " does not contain {$targetName}.");
        }
        try {
            $strB = $this->workerEntityB->getCol($sqlB, $columnIndex);
        } catch (Exception $exception) {
            $this->result[] = $this->nickNameA."  ".$exception->getMessage();
            $strB = [];
        }
        if (empty($strB)) {
            // does not exist in B
            $this->result[] = "+ " . ($this->nickNameB . " does not contain {$targetName}.");
        }

        if (!empty($strA) && !empty($strB)) {
            $strA = $strA[0];
            $strB = $strB[0];

            if ($this->ignoreTableAutoIncrement) {
                $strA = preg_replace('/\s+AUTO_INCREMENT=\d+\s+/', ' ', $strA);
                $strB = preg_replace('/\s+AUTO_INCREMENT=\d+\s+/', ' ', $strB);
            }
            if ($this->ignoreDefiner) {
                $strA = preg_replace('/\s+DEFINER=`[A-Za-z0-9_]+`@`[A-Za-z0-9_%]+`\s+/', ' ', $strA);
                $strB = preg_replace('/\s+DEFINER=`[A-Za-z0-9_]+`@`[A-Za-z0-9_%]+`\s+/', ' ', $strB);
            }
            if ($this->ignoreTableUsingBTree) {
                $strA = preg_replace('/\s+USING BTREE/', '', $strA);
                $strB = preg_replace('/\s+USING BTREE/', '', $strB);
            }
            if ($this->ignoreTableRowFormat) {
                $strA = preg_replace('/\s+ROW_FORMAT=[\w]+/', '', $strA);
                $strB = preg_replace('/\s+ROW_FORMAT=[\w]+/', '', $strB);
            }
            if (!empty($filter_rules)) {
                foreach ($filter_rules as $filter_rule) {
                    $strA = preg_replace($filter_rule['A']['rule'], $filter_rule['A']['value'], $strA);
                    $strB = preg_replace($filter_rule['B']['rule'], $filter_rule['B']['value'], $strB);
                }
            }

            $diff = MBDiff::compare($strA, $strB);

            $hasDifference = false;
            for ($i = 0; $i < count($diff); $i++) {
                if ($diff[$i][1] !== MBDiff::UNMODIFIED) {
                    $hasDifference = true;
                    break;
                }
            }
            if ($hasDifference) {
                $this->result[] = "! DIFF of " . $targetName . ": ";
                $this->result[] = PHP_EOL . MBDiff::toString($diff, "\r\n", $this->showSameLinesInDiffDetails);
            } elseif ($this->showSameCompareResults) {
                $this->result[] = "  About " . $targetName . ": SAME";
            }
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getMergedDatabaseNames()
    {
        $sql = "show databases;";
        $colsA = $this->workerEntityA->getCol($sql);
        $colsB = $this->workerEntityB->getCol($sql);
        $cols = array_merge($colsA, $colsB);
        $cols = array_unique($cols);
        return $cols;
    }

    /**
     * @param $databaseNameA
     * @param $databaseNameB
     * @throws Exception
     */
    public function compareDatabaseDDL($databaseNameA, $databaseNameB)
    {
        $sqlA = "show create database `" . $databaseNameA . "`;";
        $sqlB = "show create database `" . $databaseNameB . "`;";
        $this->fetchShowResultAndCompare("Database", $sqlA, $sqlB);
    }

    /**
     * @param null|string[] $databaseNames
     * @throws Exception
     */
    public function compareDatabasesDDL($databaseNames = null)
    {
        if ($databaseNames === null) {
            $databaseNames = $this->getMergedDatabaseNames();
        }
        if (empty($databaseNames)) {
            $this->result[] = "! No database name found in both.";
            return;
        }
        foreach ($databaseNames as $databaseName) {
            $this->compareDatabaseDDL($databaseName, $databaseName);
        }
    }

    /**
     * @param $databaseNameA
     * @param $databaseNameB
     * @param $tableName
     * @throws Exception
     */
    public function compareTableDDL($databaseNameA, $databaseNameB, $tableName)
    {
        $sqlA = "show create table `{$databaseNameA}`.`{$tableName}`;";
        $sqlB = "show create table `{$databaseNameB}`.`{$tableName}`;";
        $filter_rules = [[
            'A' => ['rule' => '/`' . $databaseNameA . '`.`([a-zA-z0-9_-]+)`/', 'value' => '`${1}`'],
            'B' => ['rule' => '/`' . $databaseNameB . '`.`([a-zA-z0-9_-]+)`/', 'value' => '`${1}`']
            ]];
        $this->fetchShowResultAndCompare("Table {$tableName}", $sqlA, $sqlB, 1, $filter_rules);
    }

    /**
     * @param $databaseNameA
     * @param $databaseNameB
     * @param null $tableNames
     * @throws Exception
     */
    public function compareTablesDDL($databaseNameA, $databaseNameB, $tableNames = null)
    {
        if ($tableNames === null) {
            try {
                $sql = "show tables in `{$databaseNameA}`;";
                $tableNamesA = $this->workerEntityA->getCol($sql, 0);
            } catch (Exception $exception) {
                $tableNamesA = [];
            }
            if (empty($tableNamesA)) {
                $this->result[] = "- " . $this->nickNameA . " does not contain tables in Database {$databaseNameA}.";
            }
            try {
                $sql = "show tables in `{$databaseNameB}`;";
                $tableNamesB = $this->workerEntityB->getCol($sql, 0);
            } catch (Exception $exception) {
                $tableNamesB = [];
            }
            if (empty($tableNamesB)) {
                $this->result[] = "+ " . $this->nickNameB . " does not contain tables in Database {$databaseNameB}.";
            }

            $tableNames = array_merge($tableNamesA, $tableNamesB);
            $tableNames = array_unique($tableNames);
        }

        if (empty($tableNames)) {
            $this->result[] = "! No table name found in both.";
            return;
        }

        foreach ($tableNames as $tableName) {
            $this->compareTableDDL($databaseNameA, $databaseNameB, $tableName);
        }
    }

    /**
     * @param $databaseNameA
     * @param $databaseNameB
     * @param $functionName
     * @throws Exception
     */
    public function compareFunctionDDL($databaseNameA, $databaseNameB, $functionName)
    {
        $sqlA = "show create function `{$databaseNameA}`.`{$functionName}`;";
        $sqlB = "show create function `{$databaseNameB}`.`{$functionName}`;";
        $this->fetchShowResultAndCompare("Function {$functionName}", $sqlA, $sqlB, 2);
    }

    /**
     * @param $databaseNameA
     * @param $databaseNameB
     * @param null $functionNames
     * @throws Exception
     */
    public function compareFunctionsDDL($databaseNameA, $databaseNameB, $functionNames = null)
    {
        if ($functionNames === null) {
            try {
                $sql = "SHOW FUNCTION STATUS where db='{$databaseNameA}';";
                $functionsA = $this->workerEntityA->getCol($sql, 'name');
            } catch (Exception $exception) {
                $functionsA = [];
            }
            if (empty($functionsA)) {
                $this->result[] = "- " . $this->nickNameA . " does not contain functions in Database {$databaseNameA}.";
            }

            try {
                $sql = "SHOW FUNCTION STATUS where db='{$databaseNameB}';";
                $functionsB = $this->workerEntityB->getCol($sql, 'name');
            } catch (Exception $exception) {
                $functionsB = [];
            }
            if (empty($functionsB)) {
                $this->result[] = "+ " . $this->nickNameB . " does not contain functions in Database {$databaseNameB}.";
            }

            $functionNames = array_merge($functionsA, $functionsB);
            $functionNames = array_unique($functionNames);
        }

        if (empty($functionNames)) {
            $this->result[] = "! No function name found in both.";
            return;
        }

        foreach ($functionNames as $functionName) {
            $this->compareFunctionDDL($databaseNameA, $databaseNameB, $functionName);
        }
    }

    /**
     * @param $databaseNameA
     * @param $databaseNameB
     * @param $procedureName
     * @throws Exception
     */
    public function compareProcedureDDL($databaseNameA, $databaseNameB, $procedureName)
    {
        $sqlA = "show create procedure `{$databaseNameA}`.`{$procedureName}`;";
        $sqlB = "show create procedure `{$databaseNameB}`.`{$procedureName}`;";
        $this->fetchShowResultAndCompare("Procedure {$procedureName}", $sqlA, $sqlB, 2);
    }

    /**
     * @param $databaseNameA
     * @param $databaseNameB
     * @param null $procedureNames
     * @throws Exception
     */
    public function compareProceduresDDL($databaseNameA, $databaseNameB, $procedureNames = null)
    {
        if ($procedureNames === null) {
            try {
                $sql = "SHOW PROCEDURE STATUS where db='{$databaseNameA}';";
                $functionsA = $this->workerEntityA->getCol($sql, 'name');
            } catch (Exception $exception) {
                $functionsA = [];
            }
            if (empty($functionsA)) {
                $this->result[] = "- " . $this->nickNameA . " does not contain procedures in Database {$databaseNameA}.";
            }

            try {
                $sql = "SHOW PROCEDURE STATUS where db='{$databaseNameB}';";
                $functionsB = $this->workerEntityB->getCol($sql, 'name');
            } catch (Exception $exception) {
                $functionsB = [];
            }
            if (empty($functionsB)) {
                $this->result[] = "+ " . $this->nickNameB . " does not contain procedures in Database {$databaseNameB}.";
            }

            $procedureNames = array_merge($functionsA, $functionsB);
            $procedureNames = array_unique($procedureNames);
        }

        if (empty($procedureNames)) {
            $this->result[] = "! No procedure name found in both.";
            return;
        }

        foreach ($procedureNames as $functionName) {
            $this->compareProcedureDDL($databaseNameA, $databaseNameB, $functionName);
        }
    }

    /**
     * @param $databaseNameA
     * @param $databaseNameB
     * @param $triggerName
     * @throws Exception
     */
    public function compareTriggerDDL($databaseNameA, $databaseNameB, $triggerName)
    {
        $sqlA = "show create trigger `{$databaseNameA}`.`{$triggerName}`;";
        $sqlB = "show create trigger `{$databaseNameB}`.`{$triggerName}`;";
        $this->fetchShowResultAndCompare("Trigger {$triggerName}", $sqlA, $sqlB, 2);
    }

    /**
     * @param $databaseNameA
     * @param $databaseNameB
     * @param null $triggerNames
     * @throws Exception
     */
    public function compareTriggersDDL($databaseNameA, $databaseNameB, $triggerNames = null)
    {
        if ($triggerNames === null) {
            try {
                $sql = "SHOW TRIGGERS IN {$databaseNameA};";
                $functionsA = $this->workerEntityA->getCol($sql, 'Trigger');
            } catch (Exception $exception) {
                $functionsA = [];
            }
            if (empty($functionsA)) {
                $this->result[] = "- " . $this->nickNameA . " does not contain triggers in Database {$databaseNameA}.";
            }

            try {
                $sql = "SHOW TRIGGERS IN {$databaseNameB};";
                $functionsB = $this->workerEntityB->getCol($sql, 'Trigger');
            } catch (Exception $exception) {
                $functionsB = [];
            }
            if (empty($functionsB)) {
                $this->result[] = "+ " . $this->nickNameB . " does not contain triggers in Database {$databaseNameB}.";
            }

            $triggerNames = array_merge($functionsA, $functionsB);
            $triggerNames = array_unique($triggerNames);
        }

        if (empty($triggerNames)) {
            $this->result[] = "! No trigger name found in both.";
            return;
        }
        foreach ($triggerNames as $triggerName) {
            $this->compareTriggerDDL($databaseNameA, $databaseNameB, $triggerName);
        }
    }

    /**
     * @return string[]
     * @throws Exception
     */
    public function quickCompareEntireRDS()
    {
        $databaseNames = $this->getMergedDatabaseNames();
        foreach ($databaseNames as $databaseName) {
            if (in_array($databaseName, ["information_schema", "mysql", "performance_schema", "sys"])) {
                continue;
            }

            $this->compareDatabaseDDL($databaseName, $databaseName);
            $this->compareTablesDDL($databaseName, $databaseName);
            $this->compareFunctionsDDL($databaseName, $databaseName);
            $this->compareProceduresDDL($databaseName, $databaseName);
            $this->compareTriggersDDL($databaseName, $databaseName);
        }
        return $this->result;
    }

    /**
     * @param $databaseNameA
     * @param $databaseNameB
     * @return string[]
     * @throws Exception
     */
    public function quickCompareDatabase($databaseNameA, $databaseNameB)
    {
       // $this->compareDatabaseDDL($databaseNameA, $databaseNameB);
        $this->compareTablesDDL($databaseNameA, $databaseNameB);
        $this->compareFunctionsDDL($databaseNameA, $databaseNameB);
        $this->compareProceduresDDL($databaseNameA, $databaseNameB);
        $this->compareTriggersDDL($databaseNameA, $databaseNameB);
        return $this->result;
    }

}
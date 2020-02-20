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
     * @param $sql
     * @param int|string $columnIndex
     */
    protected function fetchShowResultAndCompare($targetName, $sql, $columnIndex = 1)
    {
        try {
            $strA = $this->workerEntityA->getCol($sql, $columnIndex);
        } catch (Exception $exception) {
            $this->result[] = $this->nickNameA."  ".$exception->getMessage();
            $strA = [];
        }
        if (empty($strA)) {
            // does not exist in A
            $this->result[] = "- " . ($this->nickNameA . " dos not contain {$targetName}.");
        }
        try {
            $strB = $this->workerEntityB->getCol($sql, $columnIndex);
        } catch (Exception $exception) {
            $this->result[] = $this->nickNameA."  ".$exception->getMessage();
            $strB = [];
        }
        if (empty($strB)) {
            // does not exist in B
            $this->result[] = "+ " . ($this->nickNameB . " dos not contain {$targetName}.");
        }

        if (!empty($strA) && !empty($strB)) {
            $strA = $strA[0];
            $strB = $strB[0];

            // PREPROCESS
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
                $this->result[] = PHP_EOL . MBDiff::toString($diff, PHP_EOL, $this->showSameLinesInDiffDetails);
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
     * @param $databaseName
     * @throws Exception
     */
    public function compareDatabaseDDL($databaseName)
    {
        $sql = "show create database `" . $databaseName . "`;";
        $this->fetchShowResultAndCompare("Database [{$databaseName}]", $sql);
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
            $this->compareDatabaseDDL($databaseName);
        }
    }

    /**
     * @param $databaseName
     * @param $tableName
     * @throws Exception
     */
    public function compareTableDDL($databaseName, $tableName)
    {
        $sql = "show create table `{$databaseName}`.`{$tableName}`;";
        $this->fetchShowResultAndCompare("Table {$databaseName}.{$tableName}", $sql);
    }

    /**
     * @param $databaseName
     * @param null $tableNames
     * @throws Exception
     */
    public function compareTablesDDL($databaseName, $tableNames = null)
    {
        if ($tableNames === null) {
            $sql = "show tables in `{$databaseName}`;";
            try {
                $tableNamesA = $this->workerEntityA->getCol($sql, 0);
            } catch (Exception $exception) {
                $tableNamesA = [];
            }
            if (empty($tableNamesA)) {
                $this->result[] = "- " . $this->nickNameA . " does not contain tables in Database {$databaseName}.";
            }
            try {
                $tableNamesB = $this->workerEntityB->getCol($sql, 0);
            } catch (Exception $exception) {
                $tableNamesB = [];
            }
            if (empty($tableNamesB)) {
                $this->result[] = "+ " . $this->nickNameB . " does not contain tables in Database {$databaseName}.";
            }

            $tableNames = array_merge($tableNamesA, $tableNamesB);
            $tableNames = array_unique($tableNames);
        }

        if (empty($tableNames)) {
            $this->result[] = "! No table name found in both.";
            return;
        }

        foreach ($tableNames as $tableName) {
            $this->compareTableDDL($databaseName, $tableName);
        }
    }

    /**
     * @param $databaseName
     * @param $functionName
     * @throws Exception
     */
    public function compareFunctionDDL($databaseName, $functionName)
    {
        $sql = "show create function `{$databaseName}`.`{$functionName}`;";

        $this->fetchShowResultAndCompare("Function {$databaseName}.{$functionName}", $sql, 2);
    }

    /**
     * @param $databaseName
     * @param null $functionNames
     * @throws Exception
     */
    public function compareFunctionsDDL($databaseName, $functionNames = null)
    {
        if ($functionNames === null) {
            $sql = "SHOW FUNCTION STATUS where db='{$databaseName}';";

            try {
                $functionsA = $this->workerEntityA->getCol($sql, 'name');
            } catch (Exception $exception) {
                $functionsA = [];
            }
            if (empty($functionsA)) {
                $this->result[] = "- " . $this->nickNameA . " does not contain functions in Database {$databaseName}.";
            }

            try {
                $functionsB = $this->workerEntityB->getCol($sql, 'name');
            } catch (Exception $exception) {
                $functionsB = [];
            }
            if (empty($functionsB)) {
                $this->result[] = "+ " . $this->nickNameB . " does not contain functions in Database {$databaseName}.";
            }

            $functionNames = array_merge($functionsA, $functionsB);
            $functionNames = array_unique($functionNames);
        }

        if (empty($functionNames)) {
            $this->result[] = "! No function name found in both.";
            return;
        }

        foreach ($functionNames as $functionName) {
            $this->compareFunctionDDL($databaseName, $functionName);
        }
    }

    /**
     * @param $databaseName
     * @param $procedureName
     * @throws Exception
     */
    public function compareProcedureDDL($databaseName, $procedureName)
    {
        $sql = "show create procedure `{$databaseName}`.`{$procedureName}`;";
        $this->fetchShowResultAndCompare("Procedure {$databaseName}.{$procedureName}", $sql, 2);
    }

    /**
     * @param $databaseName
     * @param null $procedureNames
     * @throws Exception
     */
    public function compareProceduresDDL($databaseName, $procedureNames = null)
    {
        if ($procedureNames === null) {
            $sql = "SHOW PROCEDURE STATUS where db='{$databaseName}';";

            try {
                $functionsA = $this->workerEntityA->getCol($sql, 'name');
            } catch (Exception $exception) {
                $functionsA = [];
            }
            if (empty($functionsA)) {
                $this->result[] = "- " . $this->nickNameA . " does not contain procedures in Database {$databaseName}.";
            }

            try {
                $functionsB = $this->workerEntityB->getCol($sql, 'name');
            } catch (Exception $exception) {
                $functionsB = [];
            }
            if (empty($functionsB)) {
                $this->result[] = "+ " . $this->nickNameB . " does not contain procedures in Database {$databaseName}.";
            }

            $procedureNames = array_merge($functionsA, $functionsB);
            $procedureNames = array_unique($procedureNames);
        }

        if (empty($procedureNames)) {
            $this->result[] = "! No procedure name found in both.";
            return;
        }

        foreach ($procedureNames as $functionName) {
            $this->compareProcedureDDL($databaseName, $functionName);
        }
    }

    /**
     * @param $databaseName
     * @param $triggerName
     * @throws Exception
     */
    public function compareTriggerDDL($databaseName, $triggerName)
    {
        $sql = "show create trigger `{$databaseName}`.`{$triggerName}`;";
        $this->fetchShowResultAndCompare("Trigger {$databaseName}.{$triggerName}", $sql, 2);
    }

    /**
     * @param $databaseName
     * @param null $triggerNames
     * @throws Exception
     */
    public function compareTriggersDDL($databaseName, $triggerNames = null)
    {
        if ($triggerNames === null) {
            $sql = "SHOW TRIGGERS IN {$databaseName};";
            try {
                $functionsA = $this->workerEntityA->getCol($sql, 'Trigger');
            } catch (Exception $exception) {
                $functionsA = [];
            }
            if (empty($functionsA)) {
                $this->result[] = "- " . $this->nickNameA . " does not contain triggers in Database {$databaseName}.";
            }

            try {
                $functionsB = $this->workerEntityB->getCol($sql, 'Trigger');
            } catch (Exception $exception) {
                $functionsB = [];
            }
            if (empty($functionsB)) {
                $this->result[] = "+ " . $this->nickNameB . " does not contain triggers in Database {$databaseName}.";
            }

            $triggerNames = array_merge($functionsA, $functionsB);
            $triggerNames = array_unique($triggerNames);
        }

        if (empty($triggerNames)) {
            $this->result[] = "! No trigger name found in both.";
            return;
        }
        foreach ($triggerNames as $triggerName) {
            $this->compareTriggerDDL($databaseName, $triggerName);
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

            $this->compareDatabaseDDL($databaseName);
            $this->compareTablesDDL($databaseName);
            $this->compareFunctionsDDL($databaseName);
            $this->compareProceduresDDL($databaseName);
            $this->compareTriggersDDL($databaseName);
        }
        return $this->result;
    }

    /**
     * @param $databaseNames
     * @return string[]
     * @throws Exception
     */
    public function quickCompareDatabases($databaseNames)
    {
        if (!is_array($databaseNames)) {
            $databaseNames = [$databaseNames];
        }
        foreach ($databaseNames as $databaseName) {
            $this->compareDatabaseDDL($databaseName);
            $this->compareTablesDDL($databaseName);
            $this->compareFunctionsDDL($databaseName);
            $this->compareProceduresDDL($databaseName);
            $this->compareTriggersDDL($databaseName);
        }
        return $this->result;
    }

}
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
    public $showSameCompareResults = false;
    public $showSameLinesInDiffDetails = true;
    public $ignoreTableAutoIncrement = false;
    public $ignoreDefiner = false;
    public $ignoreTableUsingBTree = false;
    public $ignoreTableRowFormat = false;
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
            //echo $this->configA->getNick()." ".implode(";",$this->workerEntityA->getPDOErrorInfo())." ".$exception->getMessage().PHP_EOL;
            $strA = [];
        }
        if (empty($strA)) {
            // does not exist in A
            echo "- " . ($this->nickNameA . " dos not contain {$targetName}.") . PHP_EOL;
        }
        try {
            $strB = $this->workerEntityB->getCol($sql, $columnIndex);
        } catch (Exception $exception) {
            //echo $this->configB->getNick()." ".implode(";",$this->workerEntityB->getPDOErrorInfo())." ".$exception->getMessage().PHP_EOL;
            $strB = [];
        }
        if (empty($strB)) {
            // does not exist in B
            echo "+ " . ($this->nickNameB . " dos not contain {$targetName}.") . PHP_EOL;
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
                $strA = preg_replace('/\s+DEFINER=`[A-Za-z0-9_]+`@`[A-Za-z0-9_]+`\s+/', ' ', $strA);
                $strB = preg_replace('/\s+DEFINER=`[A-Za-z0-9_]+`@`[A-Za-z0-9_]+`\s+/', ' ', $strB);
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
                echo "! DIFF of " . $targetName . ": ";
                echo PHP_EOL . MBDiff::toString($diff, PHP_EOL, $this->showSameLinesInDiffDetails);
            } elseif ($this->showSameCompareResults) {
                echo "  About " . $targetName . ": SAME" . PHP_EOL;
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
//        PDOHelper::assertLegalName($databaseName);

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
            echo "! No database name found in both." . PHP_EOL;
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
//        PDOHelper::assertLegalName($databaseName);
//        PDOHelper::assertLegalName($tableName);

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
//        PDOHelper::assertLegalName($databaseName);
        if ($tableNames === null) {
            $sql = "show tables in `{$databaseName}`;";
            try {
                $tableNamesA = $this->workerEntityA->getCol($sql, 0);
            } catch (Exception $exception) {
                $tableNamesA = [];
            }
            if (empty($tableNamesA)) {
                echo "- " . $this->nickNameA . " does not contain tables in Database {$databaseName}." . PHP_EOL;
            }
            try {
                $tableNamesB = $this->workerEntityB->getCol($sql, 0);
            } catch (Exception $exception) {
                $tableNamesB = [];
            }
            if (empty($tableNamesB)) {
                echo "+ " . $this->nickNameB . " does not contain tables in Database {$databaseName}." . PHP_EOL;
            }

            $tableNames = array_merge($tableNamesA, $tableNamesB);
            $tableNames = array_unique($tableNames);
        }

        if (empty($tableNames)) {
            echo "! No table name found in both." . PHP_EOL;
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
//        PDOHelper::assertLegalName($databaseName);
//        PDOHelper::assertLegalName($functionName);

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
//        PDOHelper::assertLegalName($databaseName);
        if ($functionNames === null) {
            $sql = "SHOW FUNCTION STATUS where db='{$databaseName}';";

            try {
                $functionsA = $this->workerEntityA->getCol($sql, 'name');
            } catch (Exception $exception) {
                $functionsA = [];
            }
            if (empty($functionsA)) {
                echo "- " . $this->nickNameA . " does not contain functions in Database {$databaseName}." . PHP_EOL;
            }

            try {
                $functionsB = $this->workerEntityB->getCol($sql, 'name');
            } catch (Exception $exception) {
                $functionsB = [];
            }
            if (empty($functionsB)) {
                echo "+ " . $this->nickNameB . " does not contain functions in Database {$databaseName}." . PHP_EOL;
            }

            $functionNames = array_merge($functionsA, $functionsB);
            $functionNames = array_unique($functionNames);
        }

        if (empty($functionNames)) {
            echo "! No function name found in both." . PHP_EOL;
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
//        PDOHelper::assertLegalName($databaseName);
//        PDOHelper::assertLegalName($procedureName);

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
//        PDOHelper::assertLegalName($databaseName);
        if ($procedureNames === null) {
            $sql = "SHOW PROCEDURE STATUS where db='{$databaseName}';";

            try {
                $functionsA = $this->workerEntityA->getCol($sql, 'name');
            } catch (Exception $exception) {
                $functionsA = [];
            }
            if (empty($functionsA)) {
                echo "- " . $this->nickNameA . " does not contain procedures in Database {$databaseName}." . PHP_EOL;
            }

            try {
                $functionsB = $this->workerEntityB->getCol($sql, 'name');
            } catch (Exception $exception) {
                $functionsB = [];
            }
            if (empty($functionsB)) {
                echo "+ " . $this->nickNameB . " does not contain procedures in Database {$databaseName}." . PHP_EOL;
            }

            $procedureNames = array_merge($functionsA, $functionsB);
            $procedureNames = array_unique($procedureNames);
        }

        if (empty($procedureNames)) {
            echo "! No procedure name found in both." . PHP_EOL;
            return;
        }

        foreach ($procedureNames as $functionName) {
            $this->compareProcedureDDL($databaseName, $functionName);
        }
    }

    /**
     * @throws Exception
     */
    public function quickCompareEntireRDS()
    {
        $databaseNames = $this->getMergedDatabaseNames();
        foreach ($databaseNames as $databaseName) {
            if (in_array($databaseName, ["information_schema", "mysql", "performance_schema"])) {
                continue;
            }

            $this->compareDatabaseDDL($databaseName);
            $this->compareTablesDDL($databaseName);
            $this->compareFunctionsDDL($databaseName);
            $this->compareProceduresDDL($databaseName);
        }
    }

    /**
     * @param $databaseNames
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
        }
    }

}
<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018/12/6
 * Time: 5:10 PM
 */

namespace sinri\databasehub\controller;


use Exception;
use sinri\ark\core\ArkHelper;
use sinri\ark\database\model\ArkSQLCondition;
use sinri\databasehub\core\AbstractAuthController;
use sinri\databasehub\core\HubCore;
use sinri\databasehub\core\SQLChecker;
use sinri\databasehub\entity\ApplicationEntity;
use sinri\databasehub\entity\DatabaseEntity;
use sinri\databasehub\model\ApplicationModel;
use sinri\databasehub\model\DatabaseModel;
use sinri\databasehub\model\UserModel;
use sinri\databasehub\model\UserPermittedApprovalModel;

class ApplicationController extends AbstractAuthController
{
    /**
     * @param $application
     * @return array
     * @throws Exception
     */
    private function verifyApplication($application)
    {
        $keys = ['title', 'description', 'database_id', 'sql', 'type'];
        $data = [];
        foreach ($keys as $key) {
            if (!isset($application[$key])) {
                throw new Exception("Lack of field " . $key);
            }
            $data[$key] = $application[$key];
        }
        if (DatabaseEntity::instanceById($data['database_id'])->status !== DatabaseModel::STATUS_NORMAL) {
            throw new Exception("Target database is not normal");
        }
        if (!in_array($data['type'], [
            ApplicationModel::TYPE_DDL,
            ApplicationModel::TYPE_EXECUTE,
            ApplicationModel::TYPE_MODIFY,
            ApplicationModel::TYPE_READ,
        ])) {
            throw new Exception("Illegal Application Type");
        }

        // check SQL Syntax
        $subSQLs = SQLChecker::split($data['sql']);
        HubCore::getLogger()->debug("SQL is broken down to " . count($subSQLs) . " parts");
        if (empty($subSQLs)) {
            throw new Exception("This seems not a valid SQL, April Fool?");
        }
        foreach ($subSQLs as $subSQL) {
            $typeOfSubSQL = SQLChecker::getTypeOfSingleSql($subSQL);
            HubCore::getLogger()->debug("SQL type: " . json_encode($typeOfSubSQL), ["sql" => $subSQL]);
            if ($typeOfSubSQL === false) {
                throw new Exception("Not a valid SQL.");
            }
            switch ($data['type']) {
                case ApplicationModel::TYPE_DDL:
                    if (!in_array($typeOfSubSQL, [
                        SQLChecker::QUERY_TYPE_ALTER,
                        SQLChecker::QUERY_TYPE_CREATE,
                        SQLChecker::QUERY_TYPE_DROP,
                        SQLChecker::QUERY_TYPE_TRUNCATE,
                    ])) {
                        throw new Exception("Not a DDL statement.");
                    }
                    break;
                case ApplicationModel::TYPE_EXECUTE:
                    if (!in_array($typeOfSubSQL, [
                        SQLChecker::QUERY_TYPE_CALL,
                    ])) {
                        throw new Exception("Not an EXECUTE statement.");
                    }
                    break;
                case ApplicationModel::TYPE_MODIFY:
                    if (!in_array($typeOfSubSQL, [
                        SQLChecker::QUERY_TYPE_DELETE,
                        SQLChecker::QUERY_TYPE_INSERT,
                        SQLChecker::QUERY_TYPE_REPLACE,
                        SQLChecker::QUERY_TYPE_UPDATE,
                    ])) {
                        throw new Exception("Not a MODIFY statement.");
                    }
                    break;
                case ApplicationModel::TYPE_READ:
                    if (!in_array($typeOfSubSQL, [
                        SQLChecker::QUERY_TYPE_SHOW,
                        SQLChecker::QUERY_TYPE_EXPLAIN,
                        SQLChecker::QUERY_TYPE_SELECT,
                    ])) {
                        throw new Exception("Not a READ statement.");
                    }
                    break;
                default:
                    throw new Exception("Unknown Type");
            }

        }


        return $data;
    }

    /**
     * @throws Exception
     */
    public function create()
    {
        // Create new application
        $application = $this->_readRequest('application');
        $application = $this->verifyApplication($application);
        $application['apply_user'] = $this->session->user->userId;
        $application['create_time'] = ApplicationModel::now();
        $application['status'] = ApplicationModel::STATUS_APPLIED;
        $application_id = (new ApplicationModel())->insert($application);

        if (empty($application_id)) {
            throw new Exception("Cannot create application.");
        }

        $applicationEntity = ApplicationEntity::instanceById($application_id);
        $applicationEntity->writeRecord($this->session->user->userId, "APPLY", "");

        $this->_sayOK(['application_id' => $application_id]);
    }

    /**
     * @throws Exception
     */
    public function update()
    {
        $application_id = $this->_readRequest('application_id', '', '/^\d+$/');
        $applicationEntity = ApplicationEntity::instanceById($application_id);

        $applicationUpdate = $this->_readRequest('application');
        $applicationUpdate = $this->verifyApplication($applicationUpdate);
        $applicationUpdate['edit_time'] = ApplicationModel::now();
        $applicationUpdate['status'] = ApplicationModel::STATUS_APPLIED;

        if (!in_array($applicationEntity->status, [
            ApplicationModel::STATUS_DENIED,
            ApplicationModel::STATUS_CANCELLED,
            ApplicationModel::STATUS_ERROR,
        ])) {
            throw new Exception("Now you cannot update this application.");
        }

        if ($applicationEntity->applyUser->userId != $this->session->user->userId) {
            throw new Exception("You are not the applier.");
        }

        $afx = (new ApplicationModel())->update([
            'application_id' => $application_id,
            'status' => [
                ApplicationModel::STATUS_DENIED,
                ApplicationModel::STATUS_CANCELLED,
                ApplicationModel::STATUS_ERROR,
            ]
        ], $applicationUpdate);

        if (empty($afx)) {
            throw new Exception("Cannot update application.");
        }

        $applicationEntity = ApplicationEntity::instanceById($application_id);
        $applicationEntity->writeRecord($this->session->user->userId, "UPDATE", "");

        $this->_sayOK(['afx' => $afx]);
    }

    /**
     * @throws Exception
     */
    public function cancel()
    {
        $application_id = $this->_readRequest('application_id', '', '/^\d+$/');

        $afx = (new ApplicationModel())->update([
            'application_id' => $application_id,
            'status' => ApplicationModel::STATUS_APPLIED,
            'apply_user' => $this->session->user->userId,
        ], ['status' => ApplicationModel::STATUS_CANCELLED,]);

        if (empty($afx)) {
            throw new Exception("Cannot cancel application.");
        }

        $applicationEntity = ApplicationEntity::instanceById($application_id);
        $applicationEntity->writeRecord($this->session->user->userId, "CANCEL", "");

        $this->_sayOK(['afx' => $afx]);
    }

    /**
     * @throws Exception
     */
    public function deny()
    {
        $application_id = $this->_readRequest('application_id', '', '/^\d+$/');
        $reason = $this->_readRequest("reason", 'Who knows?');

        $applicationEntity = ApplicationEntity::instanceById($application_id);

        if ($this->session->user->userType != UserModel::USER_TYPE_ADMIN) {
            $permissions = $this->session->user->getPermissionDictionary([$applicationEntity->database->databaseId]);
            $permissions = ArkHelper::readTarget($permissions, [$applicationEntity->database->databaseId, 'permissions']);
            if (empty($permissions) || !in_array($applicationEntity->type, $permissions)) {
                throw new Exception("You have not approval permission on this application");
            }
        }

        $afx = (new ApplicationModel())->update(
            [
                'application_id' => $application_id,
                'status' => ApplicationModel::STATUS_APPLIED,
            ],
            [
                'status' => ApplicationModel::STATUS_DENIED,
                'approve_user' => $this->session->user->userId,
                'approve_time' => ApplicationModel::now(),
            ]
        );

        if (empty($afx)) {
            throw new Exception("Cannot deny application.");
        }

        $applicationEntity->refresh();
        $applicationEntity->writeRecord($this->session->user->userId, "DENY", $reason);

        $this->_sayOK(['afx' => $afx]);
    }

    /**
     * @throws Exception
     */
    public function approve()
    {
        $application_id = $this->_readRequest('application_id', '', '/^\d+$/');
        $applicationEntity = ApplicationEntity::instanceById($application_id);

        if ($this->session->user->userType != UserModel::USER_TYPE_ADMIN) {
            $permissions = $this->session->user->getPermissionDictionary([$applicationEntity->database->databaseId]);
            $permissions = ArkHelper::readTarget($permissions, [$applicationEntity->database->databaseId, 'permissions']);
            if (empty($permissions) || !in_array($applicationEntity->type, $permissions)) {
                throw new Exception("You have not approval permission on this application");
            }
        }

        $afx = (new ApplicationModel())->update(
            [
                'application_id' => $application_id,
                'status' => ApplicationModel::STATUS_APPLIED,
            ],
            [
                'status' => ApplicationModel::STATUS_APPROVED,
                'approve_user' => $this->session->user->userId,
                'approve_time' => ApplicationModel::now(),
            ]
        );

        if (empty($afx)) {
            throw new Exception("Cannot approve application.");
        }

        $applicationEntity->refresh();
        $applicationEntity->writeRecord($this->session->user->userId, "APPROVE", "");

        $this->_sayOK(['afx' => $afx]);
    }

    protected function buildFetchConditions()
    {
        $conditions = [];
        // search with any conditions
        $title = $this->_readRequest('title', '');
        $database_id = $this->_readRequest('database_id', '');
        $type = $this->_readRequest('type', []);
        $apply_user = $this->_readRequest('apply_user', '');
        $status = $this->_readRequest('status', []);

        if ($title !== '') {
            $conditions['title'] = ArkSQLCondition::makeStringContainsText('title', $title);
        }
        if ($database_id !== '') {
            $conditions['database_id'] = $database_id;
        }
        if (!empty($type)) {
            $conditions['type'] = $type;
        }
        if ($apply_user !== '') {
            $conditions['apply_user'] = $apply_user;
        }
        if (!empty($status)) {
            $conditions['status'] = $status;
        }

        return $conditions;
    }

    /**
     * @throws Exception
     */
    public function search()
    {
        $pageSize = $this->_readRequest('page_size', 10);
        $page = $this->_readRequest('page', 1);

        $conditions = $this->buildFetchConditions();

        $total = (new ApplicationModel())->selectRowsForCount($conditions);
        $rows = (new ApplicationModel())->selectRowsWithSort($conditions, "application_id desc", $pageSize, ($page - 1) * $pageSize);

        $list = [];
        if (!empty($rows)) {
            foreach ($rows as $row) {
                $list[] = ApplicationEntity::instanceByRow($row)->getAbstractForList();
            }
        }

        $this->_sayOK(['list' => $list, 'total' => $total]);
    }

    /**
     * @throws Exception
     */
    public function myApprovals()
    {
        $pageSize = $this->_readRequest('page_size', 10);
        $page = $this->_readRequest('page', 1);

        $conditions = $this->buildFetchConditions();

        $conditions['status'] = ApplicationModel::STATUS_APPLIED;

        $model = new ApplicationModel();

        if ($this->session->user->userType !== UserModel::USER_TYPE_ADMIN) {
            $conditions['permitted_user'] = $this->session->user->userId;
            $model = new UserPermittedApprovalModel();
        }

        $total = $model->selectRowsForCount($conditions);
        $rows = $model->selectRowsWithSort($conditions, "application_id desc", $pageSize, ($page - 1) * $pageSize);

        $list = [];
        if (!empty($rows)) {
            foreach ($rows as $row) {
                $list[] = ApplicationEntity::instanceByRow($row)->getAbstractForList();
            }
        }

        $this->_sayOK(['list' => $list, 'total' => $total]);
    }

    /**
     * @throws Exception
     */
    public function detail()
    {
        try {
            // fetch application detail
            $application_id = $this->_readRequest('application_id', '', '/^\d+$/');
            $applicationEntity = ApplicationEntity::instanceById($application_id);
            if (is_null($applicationEntity)) {
                throw new Exception('not find application');
            }

            $canEdit = $applicationEntity->applyUser->userId === $this->session->user->userId && in_array($applicationEntity->status, [
                    ApplicationModel::STATUS_DENIED,
                    ApplicationModel::STATUS_CANCELLED,
                    ApplicationModel::STATUS_ERROR,
                ]);
            $canCancel = $applicationEntity->applyUser->userId === $this->session->user->userId && in_array($applicationEntity->status, [
                    ApplicationModel::STATUS_APPLIED
                ]);

            $canDecide = in_array($applicationEntity->status, [
                ApplicationModel::STATUS_APPLIED
            ]);
            if ($canDecide && $this->session->user->userType != UserModel::USER_TYPE_ADMIN) {
                $permissions = $this->session->user->getPermissionDictionary([$applicationEntity->database->databaseId]);
                $permissions = ArkHelper::readTarget($permissions, [$applicationEntity->database->databaseId, 'permissions']);
                if (empty($permissions) || !in_array($applicationEntity->type, $permissions)) {
                    $canDecide = false;
                }
            }
            $detail = $applicationEntity->getDetail();
            $this->_sayOK(['application' => $detail, 'can_edit' => $canEdit, 'can_cancel' => $canCancel, 'can_decide' => $canDecide]);
        } catch (Exception $e) {
            $this->_sayFail($e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function downloadExportedContentAsCSV()
    {
        $application_id = $this->_readRequest('application_id', '', '/^\d+$/');
        $application = ApplicationEntity::instanceById($application_id);
        $csv_path = $application->getExportedFilePath();

        $downloadFileName = str_replace(['/', '\\', ':', '*', '"', '<', '>', '|', '?'], '_', "DatabaseHub_" . $application->applicationId . "_" . $application->title);
        $downloadFileName = urlencode($downloadFileName);
        $this->_getOutputHandler()->downloadFileIndirectly($csv_path, null, $downloadFileName);
    }

    public function checkWorkerStatus()
    {
        $type = $this->_readRequest("type", "html");
        exec("ps aux|grep RunDHQueue|grep -v grep", $output);
        switch ($type) {
            case "status":
                if (count($output) < 2) {
                    $this->_sayOK(["status" => "inactive", "worker_count" => 0, 'output' => $output]);
                } else {
                    $this->_sayOK(["status" => "active", "worker_count" => (count($output) - 2), 'output' => $output]);
                }
                break;
            case "html":
                echo "<pre>";
                echo implode(PHP_EOL, $output);
                echo "</pre>";
                break;
            case "json":
            default:
                $this->_sayOK(['output' => $output]);
                break;
        }
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018/12/6
 * Time: 5:10 PM
 */

namespace sinri\databasehub\controller;


use sinri\databasehub\core\AbstractAuthController;
use sinri\databasehub\entity\ApplicationEntity;
use sinri\databasehub\entity\DatabaseEntity;
use sinri\databasehub\model\ApplicationModel;
use sinri\databasehub\model\DatabaseModel;

class ApplicationController extends AbstractAuthController
{
    /**
     * @param $application
     * @return array
     * @throws \Exception
     */
    private function verifyApplication($application)
    {
        $keys = ['title', 'description', 'database_id', 'sql', 'type'];
        $data = [];
        foreach ($keys as $key) {
            if (!isset($application[$key])) {
                throw new \Exception("Lack of field " . $key);
            }
            $data[$key] = $application[$key];
        }
        if (DatabaseEntity::instanceById($data['database_id'])->status !== DatabaseModel::STATUS_NORMAL) {
            throw new \Exception("Target database is not normal");
        }
        if (!in_array($data['type'], [
            ApplicationModel::TYPE_DDL,
            ApplicationModel::TYPE_EXECUTE,
            ApplicationModel::TYPE_MODIFY,
            ApplicationModel::TYPE_READ,
        ])) {
            throw new \Exception("Illegal Application Type");
        }

        // TODO check SQL Syntax

        return $data;
    }

    /**
     * @throws \Exception
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
            throw new \Exception("Cannot create application.");
        }

        $applicationEntity = ApplicationEntity::instanceById($application_id);
        $applicationEntity->writeRecord($this->session->user->userId, "APPLY", "");

        $this->_sayOK(['application_id' => $application_id]);
    }

    /**
     * @throws \Exception
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
            throw new \Exception("Now you cannot update this application.");
        }

        if ($applicationEntity->applyUser->userId != $this->session->user->userId) {
            throw new \Exception("You are not the applier.");
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
            throw new \Exception("Cannot update application.");
        }

        $applicationEntity = ApplicationEntity::instanceById($application_id);
        $applicationEntity->writeRecord($this->session->user->userId, "UPDATE", "");

        $this->_sayOK(['afx' => $afx]);
    }

    /**
     * @throws \Exception
     */
    public function cancel()
    {
        $application_id = $this->_readRequest('application_id', '', '/^\d+$/');

        $afx = (new ApplicationModel())->update([
            'application_id' => $application_id,
            'status' => ApplicationModel::STATUS_APPLIED,
        ], ['status' => ApplicationModel::STATUS_CANCELLED,]);

        if (empty($afx)) {
            throw new \Exception("Cannot cancel application.");
        }

        $applicationEntity = ApplicationEntity::instanceById($application_id);
        $applicationEntity->writeRecord($this->session->user->userId, "CANCEL", "");

        $this->_sayOK(['afx' => $afx]);
    }

    /**
     * @throws \Exception
     */
    public function deny()
    {
        $application_id = $this->_readRequest('application_id', '', '/^\d+$/');

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
            throw new \Exception("Cannot deny application.");
        }

        $applicationEntity = ApplicationEntity::instanceById($application_id);
        $applicationEntity->writeRecord($this->session->user->userId, "DENY", "");

        $this->_sayOK(['afx' => $afx]);
    }

    /**
     * @throws \Exception
     */
    public function approve()
    {
        $application_id = $this->_readRequest('application_id', '', '/^\d+$/');

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
            throw new \Exception("Cannot approve application.");
        }

        $applicationEntity = ApplicationEntity::instanceById($application_id);
        $applicationEntity->writeRecord($this->session->user->userId, "APPROVE", "");

        $this->_sayOK(['afx' => $afx]);
    }

    public function search()
    {
        // search with any conditions
        // TODO
    }

    /**
     * @throws \Exception
     */
    public function detail()
    {
        // fetch application detail
        $application_id = $this->_readRequest('application_id', '', '/^\d+$/');
        $applicationEntity = ApplicationEntity::instanceById($application_id);
        $this->_sayOK(['application' => $applicationEntity]);
    }
}
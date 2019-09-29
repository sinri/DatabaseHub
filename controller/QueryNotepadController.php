<?php
/**
 * Created by PhpStorm.
 * User: caroltc
 * Date: 19-9-29
 * Time: 上午11:09
 */

namespace sinri\databasehub\controller;

use Exception;
use sinri\databasehub\core\AbstractAuthController;
use sinri\databasehub\entity\QueryNotepadEntity;

class QueryNotepadController extends AbstractAuthController
{
    /**
     * @var QueryNotepadEntity
     */
    private $queryNotepadEntity;

    public function __construct()
    {
        parent::__construct();
        $this->queryNotepadEntity = new QueryNotepadEntity();
    }

    /**
     * 获取当前用户全部查询记事本
     */
    public function getCurrentUserAllQueryNotepads()
    {
        $result = $this->queryNotepadEntity->getUserAllQueryNotepads($this->session->user->userId);
        $this->_sayOK(['query_notepad_list' => $result]);
    }

    /**
     * 编辑记事本
     */
    public function editUserQueryNotepad()
    {
        try {
            $id = $this->_readRequest("id", '', '/^[\d]+$/');
            $title = $this->_readRequest("title", date('y-m-d H:i:s'));
            $content = $this->_readRequest("content", '');
            if (empty($content)) throw new Exception('内容不能为空');
            $this->queryNotepadEntity->edit($id, $this->session->user->userId, $title, $content);
            $this->_sayOK();
        } catch (Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }

    /**
     * 删除
     */
    public function deleteUserQueryNotepad()
    {
        $id = $this->_readRequest("id", '', '/^[\d]+$/');
        $this->queryNotepadEntity->delete($id, $this->session->user->userId);
        $this->_sayOK();
    }

    /**
     * 新建
     */
    public function createUserQueryNotepad()
    {
        try {
            $title = $this->_readRequest("title", date('y-m-d H:i:s'));
            $content = $this->_readRequest("content", '');
            if (empty($content)) throw new Exception('内容不能为空');
            $id = $this->queryNotepadEntity->create($this->session->user->userId, $title, $content);
            $this->_sayOK(['id' => $id]);
        } catch (Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }
}
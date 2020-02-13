<?php
/**
 * Created by PhpStorm.
 * User: caroltc
 * Date: 19-9-29
 * Time: 上午11:01
 */

namespace sinri\databasehub\entity;


use sinri\databasehub\model\QueryNotepadModel;

class QueryNotepadEntity
{
    public $id;
    public $userId;
    public $title;
    public $content;
    public $create_time;
    public $update_time;

    /**
     * @var QueryNotepadModel
     */
    private $queryNotepadModel;

    public function __construct()
    {
        $this->queryNotepadModel = new QueryNotepadModel();
    }

    public function create($user_id,$title,$content)
    {
        return $this->queryNotepadModel->insert(['user_id' => $user_id, 'title' => $title, 'content' => $content, 'create_time' => date('Y-m-d H:i:s')]);
    }

    public function edit($id,$user_id,$title,$content) {
        return $this->queryNotepadModel->update(['id' => $id, 'user_id' => $user_id], ['title' => $title, 'content' => $content, 'update_time' => date('Y-m-d H:i:s')]);
    }

    public function delete($id, $user_id)
    {
        return $this->queryNotepadModel->delete(['id' => $id, 'user_id' => $user_id]);
    }

    /**
     * @param $user_id
     * @return QueryNotepadEntity[]|null
     */
    public function getUserAllQueryNotepads($user_id)
    {
        $rows = $this->queryNotepadModel->selectRows(['user_id' => $user_id], 0, 0, 'id,user_id,title,create_time,update_time');
        if (empty($rows)) return null;
        return array_map(function ($row) {
            return $this->loadEntity($row);
        }, $rows);
    }

    /**
     * @param $id
     * @param $user_id
     * @return QueryNotepadEntity|null
     */
    public function getQueryNotepadDetail($id, $user_id)
    {
        $row = $this->queryNotepadModel->selectRow(['id' => $id, 'user_id' => $user_id]);
        if (empty($row)) return null;
        return $this->loadEntity($row);
    }

    /**
     * @param $row
     * @return bool|QueryNotepadEntity
     */
    private function loadEntity($row)
    {
        if (empty($row)) return false;
        $entity = new QueryNotepadEntity();
        $entity->id = $row['id'];
        $entity->userId = $row['user_id'];
        $entity->title = $row['title'];
        $entity->content = $row['content'] ?? null;
        $entity->create_time = $row['create_time'];
        $entity->update_time = $row['update_time'];
        return $entity;
    }
}
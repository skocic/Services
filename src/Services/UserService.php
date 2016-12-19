<?php

namespace Services;

use MongoClient;
use MongoDate;
use MongoCollection;
use MongoId;

class UserService
{
    /** @var MongoClient */
    private $client;
    /** @var MongoCollection */
    private $collection;

    /**
     * UserService constructor.
     *
     * @param MongoClient $client
     * @param string          $database
     * @param string          $collection
     */
    public function __construct(MongoClient $client, $database='whow', $collection = 'users')
    {
        $this->client = $client;
        /** @var MongoCollection $collection */
        $this->collection = $client->$database->$collection;
    }

    /**
     * @param array $config
     *
     * @static
     * @return \Services\UserService
     */
    public static function create($config = [])
    {
        $host = isset($config['host']) ? $config['host'] : 'localhost:27017';
        $database = isset($config['database']) ? $config['database'] : 'whow';
        $replicaSet = isset($config['replicaSet']) ? $config['replicaSet'] : false;
            $client = new MongoClient("mongodb://$host", compact('replicaSet'));

        return new self($client, $database, 'users');
    }

    /**
     * Unblock User for given userId
     *
     * @param $userId
     *
     * @return bool
     */
    public function unblockById($userId)
    {
        $_id = new MongoId($userId);
        $user = $this->collection->findOne(compact('_id'));
        $preBlockStatus = isset($user['preBlockStatus']) ? $user['preBlockStatus'] : 'active';
        $update = [
            '$set' => [
                'status' => $preBlockStatus,
                'preBlockStatus' => null,
                'updated' => new MongoDate(),
            ],
        ];
        $updateResult = $this->collection->update(compact('_id'), $update);

        return $updateResult['updatedExisting'];
    }

}

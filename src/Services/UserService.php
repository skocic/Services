<?php

namespace Services;

use MongoDB\Client;
use MongoDB\BSON\ObjectID;
use MongoDB\Collection;

class UserService
{
    /** @var \MongoDB\Client */
    private $client;
    /** @var \MongoDB\Collection */
    private $collection;

    /**
     * UserService constructor.
     *
     * @param \MongoDB\Client $client
     * @param string          $database
     * @param string          $collection
     */
    public function __construct(Client $client, $database='whow', $collection = 'users')
    {
        $this->client = $client;
        /** @var Collection $collection */
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
        $hostList = isset($config['hosts']) ? $config['hosts'] : ['localhost'];
        $hosts = implode(',', $hostList);
        $database = isset($config['database']) ? $config['database'] : 'whow';
        $port = isset($config['port']) ? $config['port'] : '27017';
        $replicaSet = isset($config['replicaSet']) ? $config['replicaSet'] : false;

        $client = new Client("mongodb://$hosts:$port", compact('replicaSet'));

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
        $_id = new ObjectID($userId);
        $user = $this->collection->findOne(compact('_id'));
        if ($user->status === 'active') {

            return false;
        }
        $preBlockStatus = isset($user->preBlockStatus) ? $user->preBlockStatus : 'active';
        $user->status = $preBlockStatus;
        $update = [
            '$set' => [
                'status' => $preBlockStatus,
                'preBlockStatus' => null,
                'updated' => time(), // 2016-11-30 23:28:22.000Z 	2016-12-01 00:28:22
            ],
        ];
        $updateResult = $this->collection->updateOne(compact('_id'), $update);

        return $updateResult->isAcknowledged();
    }

}

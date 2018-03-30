<?php

namespace SPiD;

use Predis\Client;

class RedisAuthTokenRepository implements AuthTokenRepository
{
    private const CACHE_KEY = '\SPiD\RedisAuthTokenRepository::clientAuthToken';

    /** @var Client */
    protected $redis;

    /** @var AuthTokenRepository */
    protected $repository;

    /**
     * RedisAuthTokenRepository constructor.
     *
     * @param Client $redis
     * @param AuthTokenRepository $repository
     */
    public function __construct(Client $redis, AuthTokenRepository $repository)
    {
        $this->redis = $redis;
        $this->repository = $repository;
    }

    /**
     * @inheritDoc
     */
    public function getClientAuthToken(string $scope = ''): AuthToken
    {
        $result = $this->redis->get($this->getCacheKey($scope));
        if ($result !== null) {
            $result = unserialize($result);
        } else {
            $result = $this->repository->getClientAuthToken($scope);
            $this->redis->setex($this->getCacheKey($scope), $result->expiresIn, serialize($result));
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function refreshAccessToken(AuthToken $authToken): AuthToken
    {
        $result = $this->repository->refreshAccessToken($authToken);
        $this->redis->setex($this->getCacheKey($authToken->scope), $result->expiresIn, serialize($result));

        return $result;
    }

    private function getCacheKey(string $scope): string
    {
        return self::CACHE_KEY . ':' . $scope;
    }
}

<?php

namespace SPiD;

interface AuthTokenRepository
{
    /**
     * @param string $scope
     * @return AuthToken
     */
    public function getClientAuthToken(string $scope = ''): AuthToken;

    /**
     * @param AuthToken $authToken
     * @return AuthToken
     */
    public function refreshAccessToken(AuthToken $authToken): AuthToken;
}

<?php

namespace SPiD;

class AuthToken
{
    /** @var string */
    public $expiresIn;

    /** @var string */
    public $accessToken;

    /** @var string */
    public $refreshToken;

    /** @var string */
    public $scope;

    /**
     * AuthToken constructor.
     *
     * @param string $expiresIn
     * @param string $accessToken
     * @param string $refreshToken
     * @param string $scope
     */
    public function __construct($expiresIn, $accessToken, $refreshToken, $scope)
    {
        $this->expiresIn = $expiresIn;
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->scope = $scope;
    }
}

<?php

namespace SPiD;

class SimpleAuthTokenRepository implements AuthTokenRepository
{
    /** @var  Client */
    private $client;

    /** @var string */
    private $redirectUrl;

    /**
     * GuzzleAuthTokenRepository constructor.
     *
     * @param Client $client
     * @param string $redirectUrl
     */
    public function __construct(Client $client, $redirectUrl)
    {
        $this->client = $client;
        $this->redirectUrl = $redirectUrl;
    }

    /**
     * @inheritDoc
     */
    public function getClientAuthToken(string $scope = ''): AuthToken
    {
        $response = $this->client->post(
            'oauth/token',
            [
                'redirect_uri' => $this->redirectUrl,
                'grant_type' => 'client_credentials',
                'scope' => $scope,
                'state' => '',
            ]
        );
        $parsedResponse = json_decode($response->getBody()->getContents(), true);
        $token = new AuthToken(
            $parsedResponse['expires_in'],
            $parsedResponse['access_token'],
            $parsedResponse['refresh_token'],
            $scope
        );

        return $token;
    }

    /**
     * @inheritDoc
     */
    public function refreshAccessToken(AuthToken $authToken): AuthToken
    {
        $response = $this->client->post(
            'oauth/token',
            [
                'redirect_uri' => $this->redirectUrl,
                'grant_type' => 'refresh_token',
                'scope' => $authToken->scope,
                'state' => '',
                'refresh_token' => $authToken->refreshToken,
            ]
        );
        $parsedResponse = json_decode($response->getBody()->getContents(), true);
        $token = new AuthToken(
            $parsedResponse['expires_in'],
            $parsedResponse['access_token'],
            $parsedResponse['refresh_token'],
            $authToken->scope
        );

        return $token;
    }
}

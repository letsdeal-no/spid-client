<?php

namespace SPiD;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\ResponseInterface;
use SPiD\Exception\SignatureNotMatched;

class Client
{
    /** @var HttpClient */
    private $httpClient;

    /** @var string */
    private $clientId;

    /** @var string */
    private $clientSecret;

    /** @var string */
    private $clientSignSecret;

    /** @var AuthTokenRepository */
    private $authTokenRepository;

    /** @var AuthToken */
    private $accessToken;

    /**
     * Client constructor.
     *
     * @param string $spidUrl
     * @param string $clientId
     * @param string $clientSecret
     * @param string $clientSignSecret
     * @param AuthTokenRepository $authTokenRepository
     */
    public function __construct(
        string $spidUrl,
        string $clientId,
        string $clientSecret,
        string $clientSignSecret,
        AuthTokenRepository $authTokenRepository
    ) {
        $this->httpClient = new HttpClient(['base_uri' => $spidUrl]);
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->clientSignSecret = $clientSignSecret;
        $this->authTokenRepository = $authTokenRepository;
    }

    /**
     * @param string $url
     * @param array $formParams
     * @return ResponseInterface
     * @throws ClientException
     */
    public function post(string $url, array $formParams)
    {
        return $this->httpClient->post(
            $url,
            [
                'form_params' => array_merge(
                    [
                        'client_id' => $this->clientId,
                        'client_secret' => $this->clientSecret,
                    ],
                    $formParams
                ),
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
            ]
        );
    }

    /**
     * @param $url
     * @return ResponseInterface
     */
    public function getAuthenticated($url)
    {
        return $this->runWithRefreshToken(
            function () use ($url) {
                return $this->httpClient->get(
                    $url,
                    [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $this->accessToken->accessToken,
                        ],
                    ]
                );
            }
        );
    }

    /**
     * @param $url
     * @param array $formParams
     * @return ResponseInterface
     */
    public function postAuthenticated($url, array $formParams)
    {
        return $this->runWithRefreshToken(
            function () use ($url, $formParams) {
                return $this->httpClient->post(
                    $url,
                    [
                        'form_params' => array_merge(
                            [
                                'client_id' => $this->clientId,
                                'client_secret' => $this->clientSecret,
                            ],
                            $formParams
                        ),
                        'headers' => [
                            'Authorization' => 'Bearer ' . $this->accessToken->accessToken,
                            'Content-Type' => 'application/x-www-form-urlencoded',
                        ],
                    ]
                );
            }
        );
    }

    /**
     * @param $signed_request
     * @return mixed
     * @throws SignatureNotMatched
     */
    public function parseSignedRequest($signed_request)
    {
        list($encoded_sig, $payload) = explode('.', $signed_request, 2);
        $sig = $this->base64UrlDecode($encoded_sig);
        $data = $this->base64UrlDecode($payload);
        $expected_sig = hash_hmac('sha256', $payload, $this->clientSignSecret, $raw = true);
        if ($sig !== $expected_sig) {
            throw new SignatureNotMatched();
        }

        return json_decode($data, true);
    }

    private function runWithRefreshToken(callable $function)
    {
        if (empty($this->accessToken)) {
            $this->accessToken = $this->authTokenRepository->getClientAuthToken();
        }
        try {
            return $function();
        } catch (ClientException $exception) {
            if ($exception->getCode() === 401) {
                $this->accessToken = $this->authTokenRepository->refreshAccessToken($this->accessToken);

                return $function();
            } else {
                throw $exception;
            }
        }
    }

    private function base64UrlDecode($input)
    {
        return base64_decode(strtr($input, '-_', '+/'));
    }

    /**
     * @return AuthToken
     */
    public function getAccessToken(): AuthToken
    {
        return $this->accessToken;
    }

    /**
     * @param HttpClient $httpClient
     */
    public function setHttpClient(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }
}

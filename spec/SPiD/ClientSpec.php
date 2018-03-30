<?php

namespace spec\SPiD;

use GuzzleHttp\Client as HttpClient;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument\Token\CallbackToken;
use SPiD\AuthToken;
use SPiD\AuthTokenRepository;

class ClientSpec extends ObjectBehavior
{
    public function let(AuthTokenRepository $authTokenRepository)
    {
        $this->beConstructedWith('http://localhost', 'clientId', 'clientSecret', 'signSecret', $authTokenRepository);
    }

    public function it_should_make_post_request(HttpClient $client)
    {
        $url = '/test';
        $data = [
            'foo' => 'bar',
        ];
        $this->setHttpClient($client);
        $client->post(
            $url,
            new CallbackToken(
                function (array $postData) use ($data) {
                    return count(array_intersect($data, $postData['form_params'])) === count($data);
                }
            )
        )->shouldBeCalled();
        $this->post($url, $data);
    }

    public function it_should_run_post_with_access_token(AuthTokenRepository $authTokenRepository, HttpClient $client)
    {
        $url = '/test';
        $data = [
            'foo' => 'bar',
        ];
        $accessToken = new AuthToken(time() + 3600, 'token', 'refreshToken', '');
        $authTokenRepository->getClientAuthToken()->willReturn($accessToken);
        $this->setHttpClient($client);
        $client->post(
            $url,
            new CallbackToken(
                function (array $postData) use ($data, $accessToken) {
                    return count(array_intersect($data, $postData['form_params'])) === count($data)
                        && $postData['headers']['Authorization'] === 'Bearer ' . $accessToken->accessToken;
                }
            )
        )->shouldBeCalled();
        $this->postAuthenticated($url, $data);
    }

    public function it_should_run_get_with_access_token(AuthTokenRepository $authTokenRepository, HttpClient $client)
    {
        $url = '/test';
        $accessToken = new AuthToken(time() + 3600, 'token', 'refreshToken', '');
        $authTokenRepository->getClientAuthToken()->willReturn($accessToken);
        $this->setHttpClient($client);
        $client->get(
            $url,
            new CallbackToken(
                function (array $postData) use ($accessToken) {
                    return $postData['headers']['Authorization'] === 'Bearer ' . $accessToken->accessToken;
                }
            )
        )->shouldBeCalled();
        $this->getAuthenticated($url);
    }
}

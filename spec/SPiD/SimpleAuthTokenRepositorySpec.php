<?php

namespace spec\SPiD;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument\Token\CallbackToken;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use SPiD\AuthToken;
use SPiD\Client;

class SimpleAuthTokenRepositorySpec extends ObjectBehavior
{
    public function let(Client $client)
    {
        $this->beConstructedWith($client, 'http://redirectUri');
    }

    public function it_should_return_token(Client $client, ResponseInterface $response, StreamInterface $stream)
    {
        $response->getBody()->willReturn($stream);
        $responseJson = '{"expires_in": 1498464209, "access_token": "token", "refresh_token": "refresh_token"}';
        $stream->getContents()->willReturn($responseJson);
        $client->post(
            'oauth/token',
            new CallbackToken(
                function (array $data) {
                    return $data['grant_type'] === 'client_credentials';
                }
            )
        )->shouldBeCalled()->willReturn($response);
        $this->getClientAuthToken()->shouldReturnAnInstanceOf(AuthToken::class);
    }

    public function it_should_refresh_token(
        Client $client,
        ResponseInterface $response,
        StreamInterface $stream
    ) {
        $token = new AuthToken(time(), 'authToken', 'refreshToken', '');
        $response->getBody()->willReturn($stream);
        $responseJson = '{"expires_in": 1498464209, "access_token": "token", "refresh_token": "refresh_token"}';
        $stream->getContents()->willReturn($responseJson);
        $client->post(
            'oauth/token',
            new CallbackToken(
                function (array $data) use ($token) {
                    return $data['grant_type'] === 'refresh_token' && $data['refresh_token'] === $token->refreshToken;
                }
            )
        )->shouldBeCalled()->willReturn($response);
        $this->refreshAccessToken($token)->shouldReturnAnInstanceOf(AuthToken::class);
    }
}

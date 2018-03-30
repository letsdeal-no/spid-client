<?php

namespace spec\SPiD;

use PhpSpec\ObjectBehavior;
use Predis\Client;
use Prophecy\Argument\Token\AnyValueToken;
use SPiD\AuthToken;
use SPiD\AuthTokenRepository;

class RedisAuthTokenRepositorySpec extends ObjectBehavior
{
    public function let(Client $client, AuthTokenRepository $tokenRepository)
    {
        $this->beConstructedWith($client, $tokenRepository);
    }

    public function it_should_return_token(Client $client, AuthTokenRepository $tokenRepository, AuthToken $token)
    {
        $client->get(new AnyValueToken())->willReturn(null);
        $tokenRepository->getClientAuthToken('')->willReturn($token->getWrappedObject());
        $client->setex(new AnyValueToken(), new AnyValueToken(), serialize($token->getWrappedObject()))->shouldBeCalled(
        );
        $this->getClientAuthToken()->shouldReturn($token->getWrappedObject());
    }

    public function it_should_return_cached_token(Client $client, AuthTokenRepository $tokenRepository)
    {
        $authToken = new AuthToken(time(), 'authToken', 'refreshToken', '');
        $client->get(new AnyValueToken())->willReturn(serialize($authToken));
        $tokenRepository->getClientAuthToken('')->shouldNotBeCalled();
        $this->getClientAuthToken()->shouldBeLike($authToken);
    }

    public function it_should_return_refreshed_token(
        Client $client,
        AuthTokenRepository $tokenRepository,
        AuthToken $token,
        AuthToken $oldToken
    ) {
        $token->scope = '';
        $oldToken->scope = '';
        $tokenRepository->refreshAccessToken($oldToken->getWrappedObject())->willReturn($token->getWrappedObject());
        $client->setex(new AnyValueToken(), new AnyValueToken(), serialize($token->getWrappedObject()))->shouldBeCalled(
        );
        $this->refreshAccessToken($oldToken->getWrappedObject())->shouldReturn($token->getWrappedObject());
    }
}

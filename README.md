# SPiD client
This library provides a service-oriented client library for integration with SPiD

It provides:
## SPiD client class
`\SPiD\Client` - it utilizes Guzzle to communicate with SPiD.

It provides public methods:
1. `post(string $url, array $formParams)` - general function to send requests with proper format recognized by SPiD without authorization 
2. `getAuthenticated($url)` - sends GET request and utilizes Authorization service to obtain or refresh OAuth2 token
3. `postAuthenticated($url, array $formParams)`  - sends POST request and utilizes Authorization service to obtain or refresh OAuth2 token
4. `parseSignedRequest($signed_request)` - used to parse [SPiD signed responses](https://techdocs.spid.no/endpoints/#signed-responses)

## SPiD authorization token repository
`\SPiD\AuthTokenRepository` - used to get and refresh authorization tokens

It provides two public methods:
1. `getClientAuthToken(string $scope = ''): AuthToken` - used to retrieve authorization token
2. `refreshAccessToken(AuthToken $authToken): AuthToken` - used to refresh an existing token

There are currently two implementations of this interface:
1. `\SPiD\SimpleAuthTokenRepository` - based on Guzzle
2. `\SPiD\RedisAuthTokenRepository` - based on the one above, but caching authorization token in Redis using [predis](https://github.com/nrk/predis) library

## Installation
Add Schibsted's Artifactory to your repositories list in composer.json:
```json
    "repositories": [
        {
            "type": "composer",
            "url": "https://artifacts.schibsted.io/artifactory/api/composer/php-local"
        }
    ]
```
And run:
```
composer require letsdeal-no/spid-client:^1.0.0
```


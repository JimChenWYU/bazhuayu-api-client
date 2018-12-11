<?php

/*
 * This file is part of the jimchen/bazhuoyu-api-client.
 *
 * (c) JimChen <18219111672@163.com>
 *
 * This source file is subject to the MIT license that is bundled.
 */

namespace Bazhuayu\Support;

use Bazhuayu\Exception\RuntimeException;

trait AccessToken
{
    use HasHttpRequest, InteractsWithCache;

    /**
     * @var string
     */
    protected $tokenKey = 'access_token';

    /**
     * @var string
     */
    protected $refreshTokenKey = 'refresh_token';

    /**
     * @var string
     */
    protected $cachePrefix = 'bazhuayu.client.access_token.';

    /**
     * @var string
     */
    protected $endpoint = 'token';

    /**
     * @var int
     */
    protected $safeSeconds = 500;

    public function getAccessToken()
    {
        $token = $this->getToken();

        return $token[$this->tokenKey];
    }

    public function getToken($refresh = false)
    {
        $cacheKey = $this->getCacheKey();
        $cache = $this->getCache();

        if (!$refresh && $cache->has($cacheKey)) {
            return $cache->get($cacheKey);
        } elseif ($cache->has($cacheKey)) {
            $token = $cache->get($cacheKey);
            $token = $token = $this->requestToken([
                'refresh_token' => $token[$this->refreshTokenKey],
                'grant_type' => 'refresh_token',
            ]);
        } else {
            $token = $this->requestToken($this->getCredentials());
        }

        $this->setToken($token);

        return $token;
    }

    public function setToken(array $token)
    {
        $ok = $this->getCache()->set($this->getCacheKey(), $token, $token['expires_in'] - $this->safeSeconds);

        if (!$ok) {
            throw new RuntimeException('Failed to cache access token.');
        }

        return $this;
    }

    public function requestToken(array $credentials)
    {
        return $this->post($this->endpoint, $credentials, [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ]);
    }

    /**
     * @return string
     */
    protected function getCacheKey()
    {
        return $this->cachePrefix.md5(json_encode($this->getCredentials()));
    }

    /**
     * Credential for get token.
     *
     * @return array
     */
    abstract protected function getCredentials();
}

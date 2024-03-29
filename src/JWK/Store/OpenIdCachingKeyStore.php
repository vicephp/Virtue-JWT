<?php

namespace Virtue\JWK\Store;

use Psr\SimpleCache\CacheInterface;
use Virtue\JWK\KeyCachingStore;
use Virtue\JWK\KeySet;
use Virtue\JWK\KeyStore;
use Virtue\JWT\Token;

class OpenIdCachingKeyStore implements KeyCachingStore
{
    private $keyStore;
    private $cache;

    public function __construct(KeyStore $keyStore, CacheInterface $cache)
    {
        $this->keyStore = $keyStore;
        $this->cache = $cache;
    }

    public function getFor(Token $token): KeySet
    {
        $key = sha1($token->payload('iss', ''));
        if ($this->cache->has($key)) {
            return $this->cache->get($key);
        }

        $keySet = $this->keyStore->getFor($token);
        $this->cache->set($key, $keySet);

        return $keySet;
    }

    public function refresh(Token $token): void
    {
        $this->cache->delete(sha1($token->payload('iss', '')));
    }
}

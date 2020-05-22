<?php

namespace Galafeno\Lingo;

use ReflectionClass;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

trait AuthenticationHandler
{
    private static $cache_key;

    public function getCacheKey()
    {
        if (!self::$cache_key) {
            static::$cache_key = Str::snake(
                (new ReflectionClass($this))->getShortName()
            ) . ":jwt";
        }

        return static::$cache_key;
    }

    protected function apiKeys($config)
    {
        $key_name = array_key_first($config);
        $key_value = $config[$key_name];
        $this->withParams([$key_name => $key_value]);
    }

    protected function oauth2($config)
    {
        $jwtCacheKey = $this->getCacheKey();

        if (!Cache::has($jwtCacheKey)) {
            $auther = new static;
            $auther->sync['commands'] = [
                'oauth2' => [
                    'verb' => 'post',
                    'url' => $config['url']
                ]
            ];
            unset($auther->sync['auth']);
            unset($config['url']);
            $jwt = $auther->command('oauth2')->withPayload($config)->send();
            Cache::put($jwtCacheKey, $jwt, $jwt->expires_in);
        }

        $jwt = Cache::get($jwtCacheKey);

        $this->withHeaders([
            'Authorization' => "{$jwt->token_type} {$jwt->access_token}"
        ]);
    }
}

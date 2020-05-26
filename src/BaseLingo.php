<?php

namespace Galafeno\Lingo;

use GuzzleHttp\Client;

use Dusterio\PlainSqs\Jobs\DispatcherJob;

class BaseLingo
{
    use AuthenticationHandler;

    protected $command = '';
    protected $data = [];
    protected $params = [];
    protected $headers = [];
    protected $bindings = [];
    protected $mode = 'sync';
    protected $mockup = null;
    private $gclient;


    public function __construct()
    {
        $this->gclient = new Client();
        $this->headers = array_merge($this->headers, [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ]);
        $this->mockup = app('env') == 'testing';
    }

    public function command($command, ...$bindings)
    {
        $this->command = $command;
        $this->bindings = $bindings;

        return $this;
    }

    public function withData($data)
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    public function withParams($params)
    {
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    public function withHeaders($headers)
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    public function withMockup($mockup)
    {
        $this->mockup = $mockup;
        return $this;
    }

    public function sync()
    {
        $this->mode = 'sync';
        return $this;
    }

    public function async()
    {
        $this->mode = 'async';
        return $this;
    }

    public function send()
    {
        if ($this->mode != 'sync' && $this->mode != 'async') {
            throw new \Exception("Modo de comunicação não suportada");
        }

        if (!isset($this->{$this->mode}['commands'][$this->command])) {
            throw new \Exception("Commando não encontrado");
        }

        $commandConfig = $this->{$this->mode}['commands'][$this->command];
        if (isset($commandConfig['function'])) {
            $callable = $commandConfig['function'];
            return $this->$callable($this->bindings);
        }

        if (isset($this->{$this->mode}['auth'])) {
            foreach ($this->{$this->mode}['auth'] as $auth_mode => $auth_config) {
                $this->$auth_mode($auth_config);
            }
        }

        
        return $this->{'send' . $this->mode}($commandConfig);
    }

    protected function sendSync($commandConfig)
    {
        $verb = $commandConfig['verb'];
        $base_url = $this->sync['base_url'];
        $url = $commandConfig['url'];
        $mockupData = $commandConfig['mockup'] ?? 'static';

        foreach ($this->bindings as $binding) {
            $url = preg_replace('/{:\?}/', $binding, $url, 1);
        }

        if (!$this->mockup) {
            return json_decode(
                $this
                ->gclient
                ->request($verb, "{$base_url}{$url}", [
                    'headers' => $this->headers,
                    'query' => $this->params,
                    'json' => $this->data
                ])
                ->getBody()
                ->getContents()
            );
        } else {
            if ($mockupData == 'static') {
                return (object) $commandConfig['shouldReturn'];
            } elseif ($mockupData == 'function') {
                return $this->{$commandConfig['shouldReturn']}($this->bindings);
            } else {
                throw new \Exception("Tipo de mockup não suportado");
            }
        }
    }

    protected function sendAsync($commandConfig)
    {
        $payload = [
            'action' => $commandConfig['action'],
            'data' => $this->data
        ];
        $queue = $this->async['queue'];

        if (app('env') != 'testing') {
            dispatch(
                (new DispatcherJob($payload))
                    ->setPlain()
                    ->onQueue($queue)
            );
        }

        return (object) ['log' => 'async message sent'];
    }
}

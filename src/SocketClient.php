<?php
namespace EAMann\Machines;

use WebSocket\BadOpcodeException;
use WebSocket\Client;
use WebSocket\ConnectionException;

trait SocketClient
{
    /**
     * @var Client
     */
    protected $client = null;

    private $connected = false;

    protected function connect($host = 'localhost', $port = 8080)
    {
        $this->client = new Client(sprintf('ws://%s:%s', $host, $port));
        $this->connected = true;
    }

    protected function send($payload)
    {
        $encoded = json_encode($payload);
        if ($this->connected && $encoded !== false) {
            try {
                $this->client->send($encoded);
            } catch (BadOpcodeException $e) {
                // Squash exception as we don't really care ...
            } catch (ConnectionException $e) {
                // Squash exception as we don't really care ...
            }
        }
    }

    public function close()
    {
        if ($this->connected) {
            $this->client->close();
            $this->client = null;
            $this->connected = false;
        }
    }
}
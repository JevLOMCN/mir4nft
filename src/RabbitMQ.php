<?php

namespace RPurinton\Mir4nft;

use React\{Async, EventLoop\Loop, EventLoop\LoopInterface};
use Bunny\{Async\Client, Channel, Message};

class RabbitMQ
{
    protected ?LoopInterface $loop = null;
    private ?Client $client = null;
    private ?Channel $channel = null;
    private ?string $consumerTag = null;
    private ?string $queue = null;

    public function connect(string $queue, callable $process): mixed
    {
        $this->queue = $queue;
        $this->loop = Loop::get();
        $this->consumerTag = bin2hex(random_bytes(8));
        $this->client = new Client($this->loop, Config::get("rabbitmq")) or throw new Error('Failed to establish the client');
        $this->client = Async\await($this->client->connect()) or throw new Error('Failed to establish the connection');
        $this->channel = Async\await($this->client->channel()) or throw new Error('Failed to establish the channel');
        Async\await($this->channel->qos(0, 1)) or throw new Error('Failed to set the QoS');
        Async\await($this->channel->queueDeclare($this->queue)) or throw new Error('Failed to declare the queue');
        return Async\await($this->channel->consume($process, $this->queue, $this->consumerTag, false, true)) or throw new Error('Failed to consume the queue');
    }

    public function publish(string $queue, array $data): bool
    {
        try {
            $client = new Client(Loop::get(), Config::get("rabbitmq")) or throw new Error('Failed to establish the client');
            $client = Async\await($client->connect()) or throw new Error('Failed to establish the connection');
            $channel = Async\await($client->channel()) or throw new Error('Failed to establish the channel');
            Async\await($channel->queueDeclare($queue)) or throw new Error('Failed to declare the queue');
            Async\await($channel->publish(json_encode($data), [], '', $queue)) or throw new Error('Failed to publish the message');
        } catch (\Throwable $e) {
            throw new Error($e->getMessage());
        } finally {
            if ($channel) $channel->close();
            if ($client) $client->disconnect();
        }
        return true;
    }

    public function disconnect(): bool
    {
        if (isset($this->channel)) {
            $this->channel->cancel($this->consumerTag);
            $this->channel->queueDelete($this->queue);
            $this->channel->close();
        }
        if (isset($this->client)) {
            $this->client->disconnect();
        }
        return true;
    }

    public function __destruct()
    {
        $this->disconnect() or throw new Error('Failed to disconnect from RabbitMQ');
    }
}

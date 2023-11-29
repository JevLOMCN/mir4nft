<?php

namespace RPurinton\Mir4nft;

use React\Async;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use Bunny\Async\Client;
use Bunny\Channel;
use Bunny\Message;

class RabbitMQ
{
    private ?LoopInterface $loop;
    private ?Client $client;
    private ?Channel $channel;
    private ?string $consumerTag;

    public function __construct(private Log $log, private string $queue, private callable $callback)
    {
        $this->connect() or throw new Error('Failed to establish the connection');
    }

    public function connect(): bool
    {
        $this->loop = Loop::get();
        $this->consumerTag = bin2hex(random_bytes(8));
        $this->client = new Client($this->loop, Config::get("rabbitmq")) or throw new Error('Failed to establish the client');
        $this->client = Async\await($this->client->connect()) or throw new Error('Failed to establish the connection');
        $this->channel = Async\await($this->client->channel()) or throw new Error('Failed to establish the channel');
        Async\await($this->channel->qos(0, 1)) or throw new Error('Failed to set the QoS');
        Async\await($this->channel->queueDeclare($this->queue)) or throw new Error('Failed to declare the queue');
        $this->channel->consume($this->process(...), $this->queue, $this->consumerTag) or throw new Error('Failed to consume the queue');
        $this->log->debug("RabbitMQ consuming.") or throw new Error('Failed to log');
        return true;
    }

    public function process(Message $message, Channel $channel, Client $client)
    {
        unset($message->headers["delivery-mode"]);
        if (!isset($message->headers["Via"])) $message->headers["Via"] = "RabbitMQ";
        $message->headers["Content"] = $message->content;
        $this->log->debug("Received message", $message->headers);
        if (isset($message->headers["Die"]) && $message->headers["Die"]) {
            $this->log->info("Received die message... D: goodbye cruel world.");
            $this->log->info($this->queue . " has died. :(...");
            $channel->ack($message)->then(function () use ($client) {
                $client->disconnect();
                exit(0);
            });
        } else {
            if (($this->callback)($message->headers)) return $channel->ack($message);
            $channel->nack($message);
        }
    }

    public function publish(string $queue, array $data): bool
    {
        if (!$this->channel) {
            throw new Error('Attempted to publish to a queue without an active channel');
        }
        $this->channel->queueDeclare($queue);
        return Async\await($this->channel->publish(json_encode($data), [], '', $queue));
    }

    public function disconnect()
    {
        if (isset($this->channel)) {
            $this->channel->cancel($this->consumerTag);
            $this->channel->queueDelete($this->queue);
            $this->channel->close();
        }
        if (isset($this->client)) {
            $this->client->disconnect();
        }
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}

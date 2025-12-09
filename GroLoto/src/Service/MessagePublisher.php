<?php
namespace App\Service;

use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class MessagePublisher
{
    public function __construct(private HubInterface $hub) {}

    public function publish(string $topic, array $payload): void
    {
        $update = new Update(
            $topic,
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
        $this->hub->publish($update);
    }
}

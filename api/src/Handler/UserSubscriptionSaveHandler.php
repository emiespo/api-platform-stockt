<?php

declare(strict_types=1);

namespace App\Handler;

use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Api\UrlGeneratorInterface;
use ApiPlatform\Core\JsonLd\Serializer\ItemNormalizer;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use App\Entity\Subscription;
use App\Entity\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mercure\PublisherInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class UserSubscriptionSaveHandler implements MessageHandlerInterface
{
    private $iriConverter;

    private $serializer;

    private $publisher;

    private $resourceMetadataFactory;

    private $logger;

    public function __construct(
        IriConverterInterface $iriConverter,
        SerializerInterface $serializer,
        PublisherInterface $publisher,
        ResourceMetadataFactoryInterface $resourceMetadataFactory,
        LoggerInterface $logger
    )
    {
        $this->iriConverter = $iriConverter;
        $this->serializer = $serializer;
        $this->publisher = $publisher;
        $this->resourceMetadataFactory = $resourceMetadataFactory;
        $this->logger = $logger;
    }

    public function __invoke(Subscription $subscription): void
    {

        // Send message to Mercure hub
        $update = new Update(
            $this->iriConverter->getIriFromItem($subscription, UrlGeneratorInterface::ABS_URL),
            $this->serializer->serialize(
                $subscription,
                ItemNormalizer::FORMAT
            ),
            []
        );
        ($this->publisher)($update);
    }
}

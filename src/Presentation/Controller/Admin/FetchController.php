<?php

declare(strict_types=1);

namespace Tailr\SuluMessengerFailedQueueBundle\Presentation\Controller\Admin;

use Sulu\Component\Security\SecuredControllerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Tailr\SuluMessengerFailedQueueBundle\Domain\Query\FetchMessageInterface;

#[Route(path: '/messenger-failed-queue/{id}', name: 'tailr.messenger_failed_queue_fetch', options: ['expose' => true], methods: ['GET'])]
final class FetchController extends AbstractSecuredMessengerFailedQueueController implements SecuredControllerInterface
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly FetchMessageInterface $query,
    ) {
    }

    public function __invoke(int $id, Request $request): Response
    {
        return new JsonResponse(
            $this->serializer->serialize(
                ($this->query)($id, withDetails: true),
                'json',
                ['json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS, DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s']
            ),
            json: true
        );
    }
}
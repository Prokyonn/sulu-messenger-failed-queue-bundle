<?php

declare(strict_types=1);

namespace Tailr\SuluMessengerFailedQueueBundle\Presentation\Controller\Admin;

use function Psl\Type\int;
use function Psl\Type\shape;
use function Psl\Type\vec;

use Sulu\Component\Security\SecuredControllerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Tailr\SuluMessengerFailedQueueBundle\Domain\Command\RequeueHandler;

#[Route(path: '/messenger-failed-queue/requeue', name: 'app.messenger_failed_queue_requeue', methods: ['PUT'])]
final class RequeueController extends AbstractSecuredMessengerFailedQueueController implements SecuredControllerInterface
{
    public function __construct(
        private readonly RequeueHandler $handler,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        try {
            $data = shape([
                'identifiers' => vec(int()),
            ])->coerce(json_decode($request->getContent(), true));

            foreach ($data['identifiers'] as $id) {
                ($this->handler)($id);
            }

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        } catch (\Throwable $e) {
            return new JsonResponse(['detail' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}

<?php
declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Garage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\RequestContext;

class RequestParamObjectSubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var RequestContext
     */
    private $requestContext;

    public function __construct(EntityManagerInterface $entityManager, RequestContext $requestContext)
    {
        $this->em = $entityManager;
        $this->requestContext = $requestContext;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }

    public function onKernelRequest(GetResponseEvent $event): void
    {
        if (!$this->requestContext) {
            return;
        }
        $request = $event->getRequest();
        $request->attributes->set('vue', strpos($this->requestContext->getPathInfo(), '.vue') !== false);
    }
}

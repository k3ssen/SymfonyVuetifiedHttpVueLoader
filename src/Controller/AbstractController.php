<?php
declare(strict_types=1);

namespace App\Controller;

use App\Vue\VueDataStorage;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerTrait;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class AbstractController implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    use ControllerTrait;

    protected const MESSAGE_SAVED = 'Data has been saved successfully.';
    protected const MESSAGE_DELETED = 'Data has been deleted successfully.';
    protected const MESSAGE_CANNOT_BE_DELETED = 'Failed to delete data, because of other dependencies.';

    /** @var VueDataStorage */
    protected $vueDataStorage;

    /**
     * @required
     */
    public function setVueDataStorage(VueDataStorage $vueDataStorage)
    {
        $this->vueDataStorage = $vueDataStorage;
    }

    protected function getParameter(string $name)
    {
        return $this->container->getParameter($name);
    }

    /**
     * Overwrites 'render' in ControllerTrait, so that Vue page is returned as a single page or withing the base file.
     */
    protected function render(string $view, array $parameters = [], Response $response = null): Response
    {
        if (null === $response) {
            $response = new Response();
        }
        $pageView = $this->renderView($view, $parameters);
        $vueData = $this->vueDataStorage->getJson();
        $request = $this->container->get('request_stack')->getCurrentRequest();
        if ($request->attributes->get('vue')) {
            $response = JsonResponse::create([
                'body' => $pageView,
                'vueData' => $vueData,
            ]);
        } else {
            $response->setContent($this->renderView('base.html.twig', [
                'pageView' => json_encode([
                    'body' => $pageView,
                    'vueData' => $vueData,
                ]),
            ]));
        }
        return $response;
    }

    /**
     * Returns a RedirectResponse to the given route with the given parameters.
     * Overwrites Controller-trait to allow parameters as object as well.
     */
    protected function redirectToRoute(string $route, $parameters = [], int $status = 302): RedirectResponse
    {
        return $this->redirect($this->generateUrl($route, $parameters), $status);
    }

    /**
     * Generates a URL from the given parameters.
     * Overwrites Controller-trait to allow parameters as object as well.
     */
    protected function generateUrl(string $route, $parameters = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        return $this->container->get('router')->generate($route, $parameters, $referenceType);
    }

    /**
     * @return ObjectManager|EntityManager
     */
    protected function getEntityManager(): ObjectManager
    {
        return $this->getDoctrine()->getManager();
    }

    protected function createDeleteForm($object): FormInterface
    {
        return $this->createFormBuilder($object)
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    /**
     * @param FormInterface $form
     * @param Request $request
     * @param string $successMessage #TranslationKey
     * @return bool
     */
    protected function handleForm(FormInterface $form, Request $request, string $successMessage = null): bool
    {
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->saveObject($form->getData());
            $this->addFlash('success', $successMessage ?: static::MESSAGE_SAVED);
            return true;
        }
        return false;
    }

    /**
     * @param FormInterface $form
     * @param Request $request
     * @param string $successMessage #TranslationKey
     * @param string $failedMessage #TranslationKey
     * @return bool
     */
    protected function handleDeleteForm(FormInterface $form, Request $request, string $successMessage = null, string $failedMessage = null): bool
    {
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $object = $form->getData();
            if ($this->isDeletable($object)) {
                $this->deleteObject($object);
                $this->addFlash('success', $successMessage ?: static::MESSAGE_DELETED);
                return true;
            } else {
                $this->addFlash('error', $failedMessage ?: static::MESSAGE_CANNOT_BE_DELETED);
            }
        }
        return false;
    }

    protected function saveObject($object): void
    {
        $em = $this->getEntityManager();
        $em->persist($object);
        $em->flush();
    }

    protected function deleteObject($object): void
    {
        $em = $this->getEntityManager();
        $em->remove($object);
        $em->flush();
    }

    /**
     * Tries to delete an object without actually deleting it.
     * Returns false if ForeignKeyConstraintViolationException would be thrown; true otherwise.
     */
    protected function isDeletable($object): bool
    {
        try {
            $em = $this->getEntityManager();

            $em->beginTransaction();

            // Use query instead of $em->remove()
            // With $em->remove, the object's id will become null, which will make it hard to manage afterwards.
            $em->createQueryBuilder()
                ->delete(get_class($object), 'o')
                ->where('o = :object')
                ->setParameter('object', $object)
                ->getQuery()
                ->execute()
            ;

            $em->rollback();
            return true;
        } catch (ForeignKeyConstraintViolationException $e) {
            return false;
        }
    }
}

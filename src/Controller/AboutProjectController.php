<?php

namespace App\Controller;

use App\Entity\News;
use App\Helper\SeoHelper;
use App\Service\SeoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Exception\RuntimeException as FormSubmitException;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
};
use App\Service\ContactUsFormHandler;
use App\Entity\Contact;
/**
 * Class AboutProjectController
 * @package App\Controller
 */
class AboutProjectController extends AbstractController
{
    /**
     * @Route("/contacts", name="contacts")
     *
     * @param   Request                 $request
     * @param   TranslatorInterface     $translator
     * @param   ContactUsFormHandler    $formHandler
     *
     * @return  Response
     */
    public function contacts(
        Request                 $request,
        TranslatorInterface     $translator,
        ContactUsFormHandler    $formHandler,
        SeoService              $seoService
    ): Response {
        $companyMessage = $formHandler->buildEntity();
        $form           = $formHandler->buildForm($companyMessage);

        if ($request->isMethod('POST')) {
            try {
                $formHandler->handleFormSubmit($request, $form, $companyMessage, $translator);
                $this->addFlash('success', $translator->trans('contacts.ok_send'));
                return $this->redirectToRoute('contacts');
            } catch (FormSubmitException $exception) {

            }
        }

        $seo = $seoService->setPage(SeoHelper::PAGE_CONTACTS)->getSeo();

        $lastModified = SeoHelper::formatLastModified(
            $this->getDoctrine()->getRepository(News::class)->getLastModified()
        );

        $response = new Response();
        $response->headers->set('Last-Modified', $lastModified);

        return $this->render('smat/about_contacts.html.twig', [
            'seo'           => $seo,
            'contacts'      => $this
                ->getDoctrine()
                ->getRepository(Contact::class)
                ->findAll(),
            'requestForm'   => $form->createView(),
        ], $response);
    }
    /**
     * @Route("/about/activity", name="about_activity")
     */
    public function aboutActivity(): Response
    {
        $lastModified = SeoHelper::formatLastModified(
            $this->getDoctrine()->getRepository(News::class)->getLastModified()
        );

        $response = new Response();
        $response->headers->set('Last-Modified', $lastModified);

        return $this->render('smat/about_activity.html.twig', [], $response);
    }
    /**
     * @Route("/about/project", name="about_project")
     */
    public function aboutProject(): Response
    {
        $lastModified = SeoHelper::formatLastModified(
            $this->getDoctrine()->getRepository(News::class)->getLastModified()
        );

        $response = new Response();
        $response->headers->set('Last-Modified', $lastModified);

        return $this->render('about_project/index.html.twig', [], $response);
    }
}

<?php

namespace App\Controller;

use Symfony\{
    Bundle\FrameworkBundle\Controller\AbstractController,
    Component\Routing\Annotation\Route,
    Component\HttpFoundation\Response,
    Component\HttpKernel\Exception\NotFoundHttpException,
};
use App\Entity\{Category, Other};

/**
 * Class OtherController
 * @package App\Controller
 */
class OtherController extends AbstractController
{
    /**
     * @Route("/other/{slug}", name="other_detail")
     *
     * @param string $slug
     *
     * @return Response
     */
    public function detail(string $slug): Response
    {
        $categories = $this->getDoctrine()->getRepository(Category::class)->findActiveCategories();
        $other = $this->getDoctrine()->getRepository(Other::class)->findOneBy(['slug' => $slug]);

        if (is_null($other) or $other->getIsActive() != true) {
            throw new NotFoundHttpException();
        }

        $other->increaseViewsAmount();
        $this->getDoctrine()->getManager()->flush();

        return $this->render('other/detail.html.twig',
            [
                'categories' => $categories,
                'other' => $other,
            ]);
    }
}

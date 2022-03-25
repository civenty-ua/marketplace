<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\District;
use App\Entity\Locality;
use App\Entity\Region;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AjaxController extends AbstractController
{
    /**
     *
     * @Route("/ajax/get-districts/{region}", name="get-districts")
     * @param Region $region
     * @return JsonResponse
     */
    public function getDistricts(
        Region $region,
        Request $request
    ): JsonResponse {
        $form = $request->request->get('name');
        $form = str_replace('[region]', "", $form);
        $districtInRegion = $this->getDoctrine()
            ->getRepository(District::class)
            ->findBy(["region" => $region],['name' => 'ASC']);
        $html = $this->renderView('admin/ajax/get-districts.html.twig', ['districts' => $districtInRegion, 'form' => $form]);
        $htmlLocalities = $this->renderView('admin/ajax/get-localities.html.twig', ['localities' => [], 'form' => $form]);
        return (new JsonResponse(["select" => $html, "selectLocalities" => $htmlLocalities]))->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }

    /**
     *
     * @Route("/ajax/get-localities/{district}", name="get-localities")
     * @param District $district
     * @return JsonResponse
     */
    public function getLocalities(
        District $district,
        Request $request
    ): JsonResponse {
        $form = $request->request->get('name');
        $form = str_replace('[district]', "", $form);
        $localityInDistrict = $this->getDoctrine()
            ->getRepository(Locality::class)
            ->findBy(["district" => $district],['name' => 'ASC']);
        $html = $this->renderView('admin/ajax/get-localities.html.twig', ['localities' => $localityInDistrict, 'form' => $form]);
        return (new JsonResponse(["select" => $html]))->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }
}

<?php

namespace App\Controller\Admin\Analytic;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

abstract class AnalyticBaseController extends AbstractController
{
    protected AdminUrlGenerator $urlGenerator;
    protected string $routeName;
    protected ?string $dataShowingType = null;
    protected const VIEW_TEMPLATE = 'admin/analytic/view.html.twig';
    protected EntityManagerInterface $em;

    public function __construct(AdminUrlGenerator $urlGenerator, EntityManagerInterface $em)
    {
        $this->urlGenerator = $urlGenerator;
        $this->em = $em;

        $request = Request::createFromGlobals();
        $this->routeName = $request->query->get('routeName');
        if (isset($request->query->get('routeParams')['type']))
            $this->dataShowingType = $request->query->get('routeParams')['type'];
    }

    protected function setupAdminUrl(array $routeParams, ?string $routeName = null)
    {

        if (empty($routeName)) {
            $routeName = $this->routeName;
        }
        $this->urlGenerator->setRoute($routeName, $routeParams);

        return $this->urlGenerator->generateUrl();
    }

    protected function render(?string $view, array $parameters = [], Response $response = null): Response
    {
        if (empty($view)) {
            $view = self::VIEW_TEMPLATE;
        }

        $this->checkShow($parameters);

        $this->addActionUrls($parameters);

        return parent::render($view, $parameters, $response);
    }

    /**
     * Used for switching data representation from value to percent
     * @param array $parameters
     * @return void
     */
    protected function checkShow(array &$parameters){
        if ($parameters['show'] === 'perc') {
            $data = &$parameters['data'];

            if (isset($data['ALL'])) unset($data['ALL']);

            $data = $this->calcPerc($data);
        }
    }

    protected function calcPerc(array $data):array{
        $sum = 0;
        foreach ($data as $value) {
            $sum += $value;
        }
        foreach ($data as &$value) {
            if ($sum > 0)
                $value = $value / $sum * 100;
        }
        return $data;
    }

    /**
     * Set $parameters['url']
     * @param array $parameters - Render parameters
     * @return void
     */
    protected function addActionUrls(array &$parameters){
        $formatedValueArray = ['show' => 'value'];
        $formatedPercArray = ['show' => 'perc'];

        if(isset($this->dataShowingType))
        {
            $formatedValueArray = array_merge(['type' => $this->dataShowingType], $formatedValueArray);
            $formatedPercArray = array_merge(['type' => $this->dataShowingType], $formatedPercArray);
        }

        $parameters['url'] = [
            'value' => $this->setupAdminUrl($formatedValueArray),
            'perc' => $this->setupAdminUrl($formatedPercArray),
        ];
    }
}

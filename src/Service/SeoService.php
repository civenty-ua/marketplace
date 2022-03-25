<?php

namespace App\Service;

use App\Entity\Options;
use App\Helper\SeoHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class SeoService
{
    private EntityManagerInterface $entityManager;
    private Request $request;
    private string $page;
    private array $data = [];

    public function __construct(RequestStack $requestStack, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->request = $requestStack->getCurrentRequest();
    }

    public function setPage(string $page, array $data = [])
    {
        $this->page = $page;
        $this->data = $data;

        return $this;
    }

    public function getSeo(array $variables = [])
    {
        $seo = null;

        $seoCodes = SeoHelper::getOptionCodesByPage(
            $this->page,
            $this->request->getLocale(),
            $this->data
        );

        $seoOptions = $this->entityManager->getRepository(Options::class)->getByCode($seoCodes);

        if ($seoOptions) {
            $seo = SeoHelper::getSeoByCodeOptions($seoCodes, $seoOptions, $variables);
        }

        return $seo;
    }

    public function merge(...$seo)
    {
        $mergedSeo = [];

        foreach ($seo as $key => $singleSeo) {
            if (!is_array($singleSeo) || empty($singleSeo)) {
               unset($seo[$key]);
            }
        }

        if (!empty($seo)) {
            $mergedSeo = array_merge(...$seo);
        }

        if (empty($mergedSeo)) {
            return null;
        }

        if (!isset($mergedSeo['meta_title']) || empty($mergedSeo['meta_title'])) {
            $mergedSeo['meta_title'] = null;
        }

        if (!isset($mergedSeo['meta_description']) || empty($mergedSeo['meta_description'])) {
            $mergedSeo['meta_description'] = null;
        }

        if (!isset($mergedSeo['meta_keywords']) || empty($mergedSeo['meta_keywords'])) {
            $mergedSeo['meta_keywords'] = null;
        }

        return $mergedSeo;
    }
}

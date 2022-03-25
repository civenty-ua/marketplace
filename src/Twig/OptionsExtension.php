<?php

namespace App\Twig;

use App\Entity\Category;
use App\Entity\Options;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class OptionsExtension extends AbstractExtension
{

    public const ALL_OPTION = 'all';

    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    public function getFilters(): array
    {
        return [
            new TwigFilter('options', [$this, 'getOptions']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('options', [$this, 'getOptions']),
        ];
    }

    public function getOptions($value = OptionsExtension::ALL_OPTION)
    {
        $result = [];
        $options = $this->em->getRepository(Options::class)->findAll();
        $categories = $this->em->getRepository(Category::class)->findBy(['active' => true]);
        $result['categories'] = $categories;
        /** @var Options $item */
        foreach ($options as $item) {
            $result[$item->getCode()]['value'] = $item->getValue();
            !is_null($item->getImageName())
                ? $result[$item->getCode()]['image'] = $item->getImageName()
                : $result[$item->getCode()]['image'] = null ;
        }
        if ($value == OptionsExtension::ALL_OPTION) {
            return $result;
        } elseif (isset($result[$value])) {
            return $result[$value];
        } else {
            return '';
        }
    }
}

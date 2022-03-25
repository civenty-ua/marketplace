<?php

namespace App\Twig\Admin;

use App\Entity\User;
use App\Entity\UserToUserReview;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Security\Core\User\UserInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AdminUrlGeneratorExtension extends AbstractExtension
{

    protected AdminUrlGenerator $urlGenerator;
    protected EntityManagerInterface $em;

    public function __construct(
        EntityManagerInterface $em,
        AdminUrlGenerator      $urlGenerator
    )
    {
        $this->em = $em;
        $this->urlGenerator = $urlGenerator;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('adminUrlGenerator', [$this, 'generateUrl']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('generateUrl', [$this, 'generateUrl']),
        ];
    }


    public function generateUrl(array $data)
    {
        $this->validateData($data);
        $link = $this->urlGenerator
            ->setController($this->setController($data))
            ->setAction($this->setAction($data))
            ->set('entityId', $data['id']);
        $link = $this->setRouteParams($data, $link);

        return $link->generateUrl();
    }

    private function validateData($data)
    {
        if (!array_key_exists('action', $data)

            || !array_key_exists('id', $data)
            || !array_key_exists('crudController', $data)
        ) {
            throw new \Exception('Keys \'action\',\'id\',\'crudController\'are required.');
        }
    }

    private function setController(array $data): string
    {

        if (class_exists($data['crudController'])) {
            return $data['crudController'];
        } else {
            throw new \Exception("Class {$data['crudController']} does not exist");

        }

    }

    private function setAction(array $data): string
    {
        if (!array_key_exists('action', $data)) {
            $action = 'detail';
        } else {
            $action = $data['action'];
        }
        return $action;
    }

    private function setRouteParams(array $data, AdminUrlGenerator $link): AdminUrlGenerator
    {
        if (count($data) > 2) {
            foreach ($data as $key => $value) {
                if ($key != 'id' || $key != 'crudController' || $key != 'action') {
                    $link->set($key, $value);
                }
            }
        }
        return $link;
    }
}
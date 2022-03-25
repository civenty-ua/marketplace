<?php

namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne;

use Doctrine\ORM\Mapping\OneToOne;
use App\Repository\WebinarEstimationRepository;
/**
 * @ORM\Entity(repositoryClass=WebinarEstimationRepository::class)
 */
class WebinarEstimation
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ManyToOne(targetEntity="User")
     */
    private $user;

    /**
     * @ManyToOne(targetEntity="Item")
     */
    private $webinar;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getWebinar(): ?Webinar
    {
        return $this->webinar;
    }

    public function setWebinar(Webinar $webinar): self
    {
        $this->webinar = $webinar;

        return $this;
    }
}

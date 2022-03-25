<?php

namespace App\Entity;

use App\Repository\VideoItemWatchingRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VideoItemWatchingRepository::class)
 */
class VideoItemWatching
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=VideoItem::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $videoItem;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="integer")
     */
    private $watched;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVideoItem(): ?VideoItem
    {
        return $this->videoItem;
    }

    public function setVideoItem(?VideoItem $videoItem): self
    {
        $this->videoItem = $videoItem;

        return $this;
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

    public function getWatched(): ?int
    {
        return $this->watched;
    }

    public function setWatched(int $watched): self
    {
        $this->watched = $watched;

        return $this;
    }

    public function addWatched(int $watched): self
    {
        $alreadyWatched = $this->getWatched() ?? 0;
        $this->setWatched($alreadyWatched + $watched);

        return $this;
    }
}

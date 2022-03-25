<?php

namespace App\Entity;

use App\Repository\ActivityRepository;
use App\Traits\Trans;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;

/**
 * @ORM\Entity(repositoryClass=ActivityRepository::class)
 */
class Activity implements TranslatableInterface
{
    use TranslatableTrait;
    use Trans;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="activity")
     */
    private $users;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $string = $this->proxyCurrentLocaleTranslation('getName');
        if (is_null($string)) {
            return '';
        }
        return $string;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setActivity($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getActivity() === $this) {
                $user->setActivity(null);
            }
        }

        return $this;
    }
}

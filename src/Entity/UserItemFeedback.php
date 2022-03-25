<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection,
};
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use App\Repository\UserItemFeedbackRepository;
/**
 * @ORM\Entity(repositoryClass=UserItemFeedbackRepository::class)
 */
class UserItemFeedback implements TimestampableInterface
{
    use TimestampableTrait;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private ?User $user;

    /**
     * @ORM\ManyToOne(targetEntity=Item::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Item $item;

    /**
     * @ORM\ManyToOne(targetEntity=FeedbackForm::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private ?FeedbackForm $feedbackForm;

    /**
     * @ORM\OneToMany(targetEntity=UserItemFeedbackAnswer::class, mappedBy="userFeedback", orphanRemoval=true, cascade={"persist"})
     */
    private Collection $userFeedbackAnswers;

    public function __construct()
    {
        $this->userFeedbackAnswers = new ArrayCollection();
    }

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

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(?Item $item): self
    {
        $this->item = $item;

        return $this;
    }

    public function getFeedbackForm(): ?FeedbackForm
    {
        return $this->feedbackForm;
    }

    public function setFeedbackForm(?FeedbackForm $feedbackForm): self
    {
        $this->feedbackForm = $feedbackForm;

        return $this;
    }
    /**
     * @return Collection|UserItemFeedbackAnswer[]
     */
    public function getUserFeedbackAnswers(): Collection
    {
        return $this->userFeedbackAnswers;
    }

    public function addUserFeedbackAnswer(UserItemFeedbackAnswer $userFeedbackAnswer): self
    {
        if (!$this->userFeedbackAnswers->contains($userFeedbackAnswer)) {
            $this->userFeedbackAnswers[] = $userFeedbackAnswer;
            $userFeedbackAnswer->setUserFeedback($this);
        }

        return $this;
    }

    public function removeUserFeedbackAnswer(UserItemFeedbackAnswer $userFeedbackAnswer): self
    {
        if ($this->userFeedbackAnswers->removeElement($userFeedbackAnswer) && $userFeedbackAnswer->getUserFeedback() === $this) {
            $userFeedbackAnswer->setUserFeedback(null);
        }

        return $this;
    }
}

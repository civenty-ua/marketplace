<?php

namespace App\Entity;

use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection,
};
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserToUserFeedbackRepository;
/**
 * @ORM\Entity(repositoryClass=UserToUserFeedbackRepository::class)
 */
class UserToUserFeedback
{
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
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private ?User $targetUser;

    /**
     * @ORM\ManyToOne(targetEntity=FeedbackForm::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private ?FeedbackForm $feedbackForm;

    /**
     * @ORM\OneToMany(targetEntity=UserToUserFeedbackAnswer::class, mappedBy="userFeedback", orphanRemoval=true)
     */
    private Collection $answers;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
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

    public function getTargetUser(): ?User
    {
        return $this->targetUser;
    }

    public function setTargetUser(?User $targetUser): self
    {
        $this->targetUser = $targetUser;

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
     * @return Collection|UserToUserFeedbackAnswer[]
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addAnswer(UserToUserFeedbackAnswer $answer): self
    {
        if (!$this->answers->contains($answer)) {
            $this->answers[] = $answer;
            $answer->setUserFeedback($this);
        }

        return $this;
    }

    public function removeAnswer(UserToUserFeedbackAnswer $answer): self
    {
        if ($this->answers->removeElement($answer)) {
            if ($answer->getUserFeedback() === $this) {
                $answer->setUserFeedback(null);
            }
        }

        return $this;
    }
}

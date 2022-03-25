<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserToUserFeedbackAnswerRepository;
/**
 * @ORM\Entity(repositoryClass=UserToUserFeedbackAnswerRepository::class)
 */
class UserToUserFeedbackAnswer
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\ManyToOne(targetEntity=UserToUserFeedback::class, inversedBy="answers")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?UserToUserFeedback $userFeedback;

    /**
     * @ORM\ManyToOne(targetEntity=FeedbackFormQuestion::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private ?FeedbackFormQuestion $question;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $answer;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserFeedback(): ?UserToUserFeedback
    {
        return $this->userFeedback;
    }

    public function setUserFeedback(?UserToUserFeedback $userFeedback): self
    {
        $this->userFeedback = $userFeedback;

        return $this;
    }

    public function getQuestion(): ?FeedbackFormQuestion
    {
        return $this->question;
    }

    public function setQuestion(?FeedbackFormQuestion $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function getAnswer()
    {
        return $this->answer;
    }

    public function setAnswer($answer): self
    {
        $this->answer = $answer;

        return $this;
    }
}

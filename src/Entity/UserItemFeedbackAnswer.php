<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use App\Repository\UserItemFeedbackAnswerRepository;
/**
 * @ORM\Entity(repositoryClass=UserItemFeedbackAnswerRepository::class)
 */
class UserItemFeedbackAnswer
{
    use TimestampableTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\ManyToOne(targetEntity=UserItemFeedback::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private ?UserItemFeedback $userFeedback;

    /**
     * @ORM\ManyToOne(targetEntity=FeedbackFormQuestion::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private ?FeedbackFormQuestion $feedbackFormQuestion;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $answer;

    /**
     * @ORM\Column(type="boolean", nullable=true,options={"default" : false})
     */
    private ?bool $isActive;

    public function __toString()
    {
        $question   = $this->getFeedbackFormQuestion();
        $answer     = (string) $this->getAnswer();

        return $question
            ? "{$question->getTitle()}: $answer"
            : '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserFeedback(): ?UserItemFeedback
    {
        return $this->userFeedback;
    }

    public function setUserFeedback(?UserItemFeedback $userFeedback): self
    {
        $this->userFeedback = $userFeedback;

        return $this;
    }

    public function getFeedbackFormQuestion(): ?FeedbackFormQuestion
    {
        return $this->feedbackFormQuestion;
    }

    public function setFeedbackFormQuestion(?FeedbackFormQuestion $feedbackFormQuestion): self
    {
        $this->feedbackFormQuestion = $feedbackFormQuestion;

        return $this;
    }

    public function getAnswer()
    {
        return $this->answer;
    }

    public function setAnswer($answer): self
    {
        $this->answer = $this->validateAnswer($answer);

        return $this;
    }
    /**
     * @return mixed
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * @param mixed $isActive
     */
    public function setIsActive($isActive): void
    {
        $this->isActive = $isActive;
    }
    /**
     * Validate answer according to question type.
     *
     * @param   mixed $value                Answer value.
     *
     * @return  mixed                       Answer validated.
     */
    private function validateAnswer($value)
    {
        if (!$this->getFeedbackFormQuestion()) {
            return null;
        }

        switch ($this->getFeedbackFormQuestion()->getType()) {
            case FeedbackFormQuestion::TYPE_STRING:
            case FeedbackFormQuestion::TYPE_REVIEW:
                return (string) $value;
            case FeedbackFormQuestion::TYPE_NUMBER:
                if (!is_numeric($value)) {
                    return null;
                }

                return strpos($value, '.') !== false
                    ? (float)   $value
                    : (int)     $value;
            case FeedbackFormQuestion::TYPE_RATE:
                if (!is_numeric($value)) {
                    return FeedbackFormQuestion::RATE_VALUE_MIN;
                }

                $valueInteger = (int) $value;

                return
                    $valueInteger < FeedbackFormQuestion::RATE_VALUE_MIN ||
                    $valueInteger > FeedbackFormQuestion::RATE_VALUE_MAX
                        ? FeedbackFormQuestion::RATE_VALUE_MIN
                        : $valueInteger;
            default:
                return null;
        }
    }
}

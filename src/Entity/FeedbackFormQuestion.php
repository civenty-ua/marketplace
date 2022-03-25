<?php

namespace App\Entity;

use InvalidArgumentException;
use App\Repository\FeedbackFormQuestionRepository;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;
use App\Traits\Trans;

/**
 * @ORM\Entity(repositoryClass=FeedbackFormQuestionRepository::class)
 */
class FeedbackFormQuestion implements TranslatableInterface
{
    use TranslatableTrait;
    use Trans;

    public const TYPE_STRING    = 'string';
    public const TYPE_NUMBER    = 'number';
    public const TYPE_RATE      = 'rate';
    public const TYPE_REVIEW    = 'review';

    public const RATE_VALUE_MIN = 0;
    public const RATE_VALUE_MAX = 5;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=FeedbackForm::class, inversedBy="feedbackFormQuestions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $feedbackForm;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $type;

    /**
     * @ORM\Column(type="boolean")
     */
    private $required = false;

    /**
     * @ORM\Column(type="integer")
     */
    private $sort;

    public static function getAllowedTypes(): array
    {
        return [
            self::TYPE_STRING,
            self::TYPE_NUMBER,
            self::TYPE_RATE,
            self::TYPE_REVIEW,
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        if (!in_array($type, self::getAllowedTypes())) {
            throw new InvalidArgumentException("unknown type $type");
        }

        $this->type = $type;

        return $this;
    }

    public function getRequired(): bool
    {
        return $this->required ?? false;
    }

    public function setRequired(bool $required): self
    {
        $this->required = $required;

        return $this;
    }

    public function getSort(): ?int
    {
        return $this->sort;
    }

    public function setSort(int $sort): self
    {
        $this->sort = $sort;

        return $this;
    }
}

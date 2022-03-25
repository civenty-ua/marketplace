<?php

namespace App\Entity;

use App\Repository\FeedbackFormRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;
use App\Traits\Trans;

/**
 * @ORM\Entity(repositoryClass=FeedbackFormRepository::class)
 */
class FeedbackForm implements TranslatableInterface
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
     * @ORM\OneToMany(targetEntity=Item::class, mappedBy="feedbackForm")
     */
    private $items;

    /**
     * @ORM\OneToMany(targetEntity=FeedbackFormQuestion::class, mappedBy="feedbackForm", orphanRemoval=true, cascade={"persist"})
     */
    private $feedbackFormQuestions;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->feedbackFormQuestions = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->proxyCurrentLocaleTranslation('getTitle') ?? '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|Item[]
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(Item $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items[] = $item;
            $item->setFeedbackForm($this);
        }

        return $this;
    }

    public function removeItem(Item $item): self
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getFeedbackForm() === $this) {
                $item->setFeedbackForm(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|FeedbackFormQuestion[]
     */
    public function getFeedbackFormQuestions(): Collection
    {
        return $this->feedbackFormQuestions;
    }

    public function addFeedbackFormQuestion(FeedbackFormQuestion $feedbackFormQuestion): self
    {
        if (!$this->feedbackFormQuestions->contains($feedbackFormQuestion)) {
            $this->feedbackFormQuestions[] = $feedbackFormQuestion;
            $feedbackFormQuestion->setFeedbackForm($this);
        }

        return $this;
    }

    public function removeFeedbackFormQuestion(FeedbackFormQuestion $feedbackFormQuestion): self
    {
        if ($this->feedbackFormQuestions->removeElement($feedbackFormQuestion)) {
            // set the owning side to null (unless already changed)
            if ($feedbackFormQuestion->getFeedbackForm() === $this) {
                $feedbackFormQuestion->setFeedbackForm(null);
            }
        }

        return $this;
    }
}

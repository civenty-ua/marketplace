<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;
use App\Repository\VideoItemRepository;
use App\Traits\Trans;
use App\Validator as AcmeAssert;

/**
 * @ORM\Entity(repositoryClass=VideoItemRepository::class)
 * @AcmeAssert\YouTubeUrl()
 */
class VideoItem implements TranslatableInterface, TimestampableInterface
{
    public const YOUTUBE_VIDEO_PAGE_LINK = 'https://www.youtube.com/embed/';
    public const YOUTUBE_VIDEO_ID_QUERY_PARAMETER = 'v';

    use TranslatableTrait;
    use TimestampableTrait;
    use Trans;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $videoId;

    /**
     * @ORM\Column(type="integer")
     */
    private $videoDuration;

    /**
     * @ORM\OneToMany(targetEntity=Webinar::class, mappedBy="videoItem")
     */
    private $webinars;

    /**
     * @ORM\OneToMany(targetEntity=Lesson::class, mappedBy="videoItem")
     */
    private $lessons;

    /**
     * @ORM\OneToMany(targetEntity=Other::class, mappedBy="videoItem")
     */
    private $others;


    public function __construct()
    {
        $this->webinars = new ArrayCollection();
        $this->lessons = new ArrayCollection();
        $this->others = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->proxyCurrentLocaleTranslation('getTitle');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVideoId(): ?string
    {
        return $this->videoId;
    }

    public function setVideoId(string $videoId): self
    {
        $this->videoId = $videoId;

        return $this;
    }

    public function getVideoDuration(): ?int
    {
        return $this->videoDuration;
    }

    public function setVideoDuration(?int $videoDuration): self
    {
        $this->videoDuration = $videoDuration;

        return $this;
    }

    public function getVideoLink(): ?string
    {
        $videoIdParameterName = self::YOUTUBE_VIDEO_ID_QUERY_PARAMETER;
        $videoPageLink = self::YOUTUBE_VIDEO_PAGE_LINK;
        $videoId = $this->getVideoId();

        return $videoId
            ? "$videoPageLink$videoId"
            : null;
    }

    public function setVideoLink(string $videoLink): self
    {
        $videoLinkParts = parse_url($videoLink);
        $videoLinkQuery = $videoLinkParts['query'] ?? '';
        $videoIdQueryParameter = self::YOUTUBE_VIDEO_ID_QUERY_PARAMETER;
        $videoLinkParameters = [];
        parse_str($videoLinkQuery, $videoLinkParameters);
        $youtubeVideoId = $videoLinkParameters[$videoIdQueryParameter] ?? '';

        $this->setVideoId($youtubeVideoId);

        return $this;
    }

    /**
     * @return Collection|Webinar[]
     */
    public function getWebinars(): Collection
    {
        return $this->webinars;
    }

    public function addWebinar(Webinar $webinar): self
    {
        if (!$this->webinars->contains($webinar)) {
            $this->webinars[] = $webinar;
            $webinar->setVideoItem($this);
        }

        return $this;
    }

    public function removeWebinar(Webinar $webinar): self
    {
        if ($this->webinars->removeElement($webinar)) {
            // set the owning side to null (unless already changed)
            if ($webinar->getVideoItem() === $this) {
                $webinar->setVideoItem(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Lesson[]
     */
    public function getLessons(): Collection
    {
        return $this->lessons;
    }

    public function addLesson(Lesson $lesson): self
    {
        if (!$this->lessons->contains($lesson)) {
            $this->lessons[] = $lesson;
            $lesson->setVideoItem($this);
        }

        return $this;
    }

    public function removeLesson(Lesson $lesson): self
    {
        if ($this->lessons->removeElement($lesson)) {
            // set the owning side to null (unless already changed)
            if ($lesson->getVideoItem() === $this) {
                $lesson->setVideoItem(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection|Other[]
     */
    public function getOthers(): Collection
    {
        return $this->others;
    }

    public function addOther(Other $other): self
    {
        if (!$this->others->contains($other)) {
            $this->others[] = $other;
            $other->setVideoItem($this);
        }

        return $this;
    }

    public function removeOther(Other $other): self
    {
        if ($this->others->removeElement($other)) {
            // set the owning side to null (unless already changed)
            if ($other->getVideoItem() === $this) {
                $other->setVideoItem(null);
            }
        }

        return $this;
    }
}

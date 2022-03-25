<?php

namespace App\Entity;

use RuntimeException;
use SplFileInfo;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;
use App\Repository\WebinarRepository;
use App\Traits\{
    EmptyField,
    Trans,
};
use App\Validator as AcmeAssert;
use App\Repository\OccurrenceRepository;
use App\Service\FileManager\FileManagerInterface;
/**
 * @ORM\Entity(repositoryClass=OccurrenceRepository::class)
 * @AcmeAssert\TranslationLength()
 */
class Occurrence extends Item implements TranslatableInterface
{
    use TranslatableTrait;
    use Trans;
    use EmptyField;

    /**
     * @ORM\ManyToOne(targetEntity=VideoItem::class, inversedBy="webinars")
     */
    private $videoItem;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $imageName;


    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    public function getTypeItem(): string
    {
        return self::OCCURRENCE;
    }

    public function __toString(): string
    {
        return $this->proxyCurrentLocaleTranslation('getTitle');
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

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): self
    {
        $this->imageName = $imageName;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCopy(
        ParameterBagInterface   $parameters,
        FileManagerInterface    $fileManager
    ): self {
        $result = parent::getCopy($parameters, $fileManager);

        $result->setVideoItem($this->getVideoItem());
        $result->copyTranslations($this);

        if ($this->getImageName()) {
            $kernelRoot             = $parameters->get('kernel.project_dir');
            $imagesDirectoryPath    = $parameters->get('app.entity.files.occurrence');
            $imagesDirectory        = new SplFileInfo($kernelRoot.$imagesDirectoryPath);
            $image                  = new SplFileInfo(
                $imagesDirectory->getPathname().
                DIRECTORY_SEPARATOR.
                $this->getImageName()
            );

            try {
                $imageCopy = $fileManager->copyEntityFile($image, $imagesDirectory);
                $result->setImageName($imageCopy->getFilename());
            } catch (RuntimeException $exception) {

            }
        }

        return $result;
    }
}

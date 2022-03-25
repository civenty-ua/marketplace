<?php
declare(strict_types=1);

namespace App\Entity;

use RuntimeException;
use SplFileInfo;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\{
    ArrayCollection,
    Collection,
};
use App\Repository\NewsRepository;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;
use Vich\UploaderBundle\{
    Entity\File,
    Mapping\Annotation as Vich
};
use App\Traits\{
    EmptyField,
    Trans,
};
use App\Service\FileManager\FileManagerInterface;
use App\Validator as AcmeAssert;
/**
 * @ORM\Entity(repositoryClass=NewsRepository::class)
 * @AcmeAssert\TranslationLength()
 * @Vich\Uploadable
 */
class News extends Item implements TranslatableInterface
{
    use TranslatableTrait;
    use Trans;
    use EmptyField;

    /**
     * @Vich\UploadableField(mapping="news_image", fileNameProperty="imageName")
     *
     * @var File|null
     */
    private $imageFile;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string|null
     */
    private $imageName;

    /**
     * @ORM\ManyToOne(targetEntity=Region::class, inversedBy="news")
     */
    private $region;

    /**
     * @ORM\ManyToMany(targetEntity=Item::class, inversedBy="similarNewsRelation")
     */
    private $similar;

    public function __construct()
    {
        parent::__construct();
        $this->similar = new ArrayCollection();
    }

    /**
     * @inheritDoc
     */
    public function getTypeItem(): string
    {
        return self::NEWS;
    }

    public function __toString(): string
    {
        return $this->proxyCurrentLocaleTranslation('getTitle');
    }

    public function setImageFile($imageFile = null): void
    {

        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile()
    {
        return $this->imageFile;
    }

    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function getRegion(): ?Region
    {
        return $this->region;
    }

    public function setRegion(?Region $region): self
    {
        $this->region = $region;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCopy(
        ParameterBagInterface $parameters,
        FileManagerInterface $fileManager
    ): self {
        $result = parent::getCopy($parameters, $fileManager);

        $result->setTypePage($this->getTypePage());
        $result->setRegion($this->getRegion());
        $result->copyTranslations($this);

        if ($this->getImageName()) {
            $kernelRoot = $parameters->get('kernel.project_dir');
            $imagesDirectoryPath = $parameters->get('app.entity.files.article');
            $imagesDirectory = new SplFileInfo($kernelRoot . $imagesDirectoryPath);
            $image = new SplFileInfo(
                $imagesDirectory->getPathname() .
                DIRECTORY_SEPARATOR .
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

    /**
     * @return Collection|self[]
     */
    public function getSimilar(): Collection
    {
        return $this->similar;
    }

    public function addSimilar(?Item $similar): self
    {
        if (!$this->similar->contains($similar)) {
            $this->similar[] = $similar;
        }

        return $this;
    }

    public function removeSimilar(self $similar): self
    {
        $this->similar->removeElement($similar);

        return $this;
    }

    public function getItemCropsAndCategoriesIds():array
    {
        $data = [];
        if ($this->getCategory()) $data['categoryId'] = $this->getCategory();
        if (!$this->getCrops()->isEmpty()) {
            foreach ($this->getCrops() as $crop) {
                $data['cropsIds'][] = $crop->getId();
            }
        }
        $data['id'] = $this->getId();

        return $data;
    }
}

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

use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;
use Vich\UploaderBundle\{
    Entity\File,
    Mapping\Annotation as Vich
};
use App\Repository\ArticleRepository;
use App\Traits\{
    EmptyField,
    Trans,
};
use App\Service\FileManager\FileManagerInterface;
use App\Validator as AcmeAssert;
/**
 * @ORM\Entity(repositoryClass=ArticleRepository::class)
 * @AcmeAssert\TranslationLength()
 * @Vich\Uploadable
 */
class Article extends Item implements TranslatableInterface
{
    use TranslatableTrait;
    use Trans;
    use EmptyField;

    /**
     * @Vich\UploadableField(mapping="article_image", fileNameProperty="imageName")
     *
     * @var File|null
     */
    private $imageFile;

    /**
     * @ORM\ManyToOne(targetEntity=TypePage::class, inversedBy="articles")
     * @ORM\JoinColumn(nullable=false)
     */
    private $typePage;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string|null
     */
    private $imageName;

    /**
     * @ORM\ManyToOne(targetEntity=Region::class, inversedBy="articles")
     */
    private $region;

    /**
     * @ORM\ManyToMany(targetEntity=Article::class, inversedBy="similarRelation")
     */
    private $similar;

    /**
     * @ORM\ManyToMany(targetEntity=Article::class, mappedBy="similar")
     */
    private $similarRelation;

    public function __construct()
    {
        parent::__construct();
        $this->similar = new ArrayCollection();
        $this->similarRelation = new ArrayCollection();
    }

    /**
     * @inheritDoc
     */
    public function getTypeItem(): string
    {
        return self::ARTICLE;
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

    public function getTypePage(): ?TypePage
    {
        return $this->typePage;
    }

    public function setTypePage(?TypePage $typePage): self
    {
        $this->typePage = $typePage;

        return $this;
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

    public function addSimilar(self $similar): self
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

    /**
     * @return Collection|self[]
     */
    public function getSimilarRelation(): Collection
    {
        return $this->similarRelation;
    }

    public function addSimilarRelation(self $similar): self
    {
        if (!$this->similarRelation->contains($similar)) {
            $this->similarRelation[] = $similar;
        }

        return $this;
    }

    public function removeSimilarRelation(self $similar): self
    {
        $this->similarRelation->removeElement($similar);

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

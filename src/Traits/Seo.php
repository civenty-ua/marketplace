<?php
namespace App\Traits;

use Doctrine\ORM\Mapping as ORM;

trait Seo
{
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $metaTitle;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $keywords;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(?string $metaTitle): self
    {
        $this->metaTitle = $metaTitle;

        return $this;
    }

    public function getKeywords(): ?string
    {
        return $this->keywords;
    }

    public function setKeywords(?string $keywords): self
    {
        $this->keywords = $keywords;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function copySeo($from, $to)
    {
        $to->setMetaTitle($from->getMetaTitle());
        $to->setKeywords($from->getKeywords());
        $to->setDescription($from->getDescription());

        return $to;
    }

    public function getSeo()
    {
        $seo = [];

        if ($this->getMetaTitle()) {
            $seo['meta_title'] = $this->getMetaTitle();
        }

        if ($this->getDescription()) {
            $seo['meta_description'] = $this->getDescription();
        }

        if ($this->getKeywords()) {
            $seo['meta_keywords'] = $this->getKeywords();
        }

        return $seo;
    }
}

<?php
namespace App\Traits;

use Doctrine\ORM\Mapping as ORM;

trait Meta
{
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $metaTitle;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $metaKeywords;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $metaDescription;

    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(?string $metaTitle): self
    {
        $this->metaTitle = $metaTitle;

        return $this;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $metaDescription): self
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    public function getMetaKeywords(): ?string
    {
        return $this->metaKeywords;
    }

    public function setMetaKeywords(?string $metaKeywords): self
    {
        $this->metaKeywords = $metaKeywords;

        return $this;
    }

    public function getSeo()
    {
        $seo = [];

        if ($this->getMetaTitle()) {
            $seo['meta_title'] = $this->getMetaTitle();
        }

        if ($this->getMetaDescription()) {
            $seo['meta_description'] = $this->getMetaDescription();
        }

        if ($this->getMetaKeywords()) {
            $seo['meta_keywords'] = $this->getMetaKeywords();
        }

        return $seo;
    }
}

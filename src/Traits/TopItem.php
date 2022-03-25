<?php
namespace App\Traits;

use Doctrine\ORM\Mapping as ORM;

trait TopItem {

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $top;


    public function getTop(): ?bool
    {
        return $this->top;
    }

    public function setTop(?bool $top): self
    {
        $this->top = $top;

        return $this;
    }
}
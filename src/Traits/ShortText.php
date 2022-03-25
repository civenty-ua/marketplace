<?php
namespace App\Traits;

use phpDocumentor\Reflection\Types\Boolean;

trait ShortText {

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $short;


    public function getShort(): ?string
    {
        return $this->short;
    }

    public function setShort(?string $short): self
    {
        $this->short = $short;

        return $this;
    }
}
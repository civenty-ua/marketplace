<?php
namespace App\Traits;

use phpDocumentor\Reflection\Types\Boolean;

trait EmptyField {

    public function getEmptyField()
    {
        return '';
    }

    public function setEmptyField($emptyField): self
    {
        return $this;
    }
}
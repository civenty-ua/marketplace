<?php

namespace App\Event\Item;

use Symfony\Component\HttpKernel\Event\RequestEvent as HttpRequestEvent;
use App\Entity\Item;

class RequestEvent extends HttpRequestEvent
{
    private $item;
    /**
     * Get item entity, if any.
     *
     * @return Item|null                    Item entity.
     */
    public function getItem(): ?Item
    {
        return $this->item;
    }
    /**
     * Set/bind item entity
     *
     * @param   Item|null $item             Item entity.
     *
     * @return  void
     */
    public function setItem(?Item $item): void
    {
        $this->item = $item;
    }
}
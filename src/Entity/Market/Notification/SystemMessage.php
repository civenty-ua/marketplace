<?php

namespace App\Entity\Market\Notification;

use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity()
 * @ORM\Table(name="market_notification_system_message")
 */
class SystemMessage  extends Notification
{
    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private bool $isSystem = true;

    public function getIsSystem(): ?bool
    {
        return $this->isSystem;
    }

    public function setIsSystem(?bool $isSystem): self
    {
        $this->isSystem = $isSystem;

        return $this;
    }

    public function getType(): string
    {
        return 'Система';
    }
}

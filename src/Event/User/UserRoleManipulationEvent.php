<?php
declare(strict_types=1);

namespace App\Event\User;

use Symfony\Contracts\EventDispatcher\Event;
use App\Entity\User;
/**
 * Any manipulations with users roles basic event class.
 */
abstract class UserRoleManipulationEvent extends Event
{
    private User    $user;
    private string  $role;

    public function __construct(User $user, string $role)
    {
        $this->user = $user;
        $this->role = $role;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getRole(): string
    {
        return $this->role;
    }
}

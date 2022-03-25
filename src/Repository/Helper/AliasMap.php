<?php
declare(strict_types = 1);

namespace App\Repository\Helper;
/**
 * Query builder alias map helper.
 */
class AliasMap
{
    private array $aliasMap = [];
    /**
     * Set alias value.
     *
     * @param   string  $name               Alias name.
     * @param   string  $value              Alias value.
     *
     * @return  self                        Itself.
     */
    public function setAlias(string $name, string $value): self
    {
        $this->aliasMap[$name] = $value;

        return $this;
    }
    /**
     * Get alias value.
     *
     * @param   string $name               Alias name.
     *
     * @return  string                     Alias value.
     */
    public function getAlias(string $name): string
    {
        return $this->aliasMap[$name] ?? '';
    }
}

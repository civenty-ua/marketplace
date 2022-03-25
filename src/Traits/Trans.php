<?php
namespace App\Traits;

use Throwable;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;

trait Trans
{
    public function __call($method, $arguments)
    {
        $method         = 'get' === substr($method, 0, 3) || 'set' === substr($method, 0, 3)
            ? $method
            : 'get'. ucfirst($method);
        $hasSuchMethod  = $this->checkHasMethod($method);

        return $hasSuchMethod
            ? $this->proxyCurrentLocaleTranslation($method, $arguments)
            : null;
    }

    public function __get($name)
    {
        $method         = 'get'. ucfirst($name);
        $hasSuchMethod  = $this->checkHasMethod($method);

        return $hasSuchMethod
            ? $this->proxyCurrentLocaleTranslation($method, [])
            : null;
    }

    public function copyTranslations(TranslatableInterface $entity)
    {
        foreach ($entity->getTranslations() as $translation) {
            $newTranslation = $translation->getCopy();
            $newTranslation->setLocale($translation->getLocale());

            $this->addTranslation($newTranslation);
            $newTranslation->setTranslatable($this);
        }
    }

    private function checkHasMethod(string $method): bool
    {
        $result = method_exists(static::class, $method);

        if (!$result) {
            try {
                $translationClass   = static::getTranslationEntityClass();
                $result             = method_exists($translationClass, $method);
            } catch (Throwable $exception) {

            }
        }

        return $result;
    }
}
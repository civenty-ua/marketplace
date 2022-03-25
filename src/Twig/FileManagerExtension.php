<?php

namespace App\Twig;

use App\Service\FileManager\FileManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FileManagerExtension extends AbstractExtension
{
    private FileManagerInterface $fileManager;

    public function __construct(FileManagerInterface $fileManager)
    {
        $this->fileManager = $fileManager;
    }

    public function getFunctions(): array
    {
        $functions = $this->fileManager->getTwigFunctions();

        return array_merge([
            new TwigFunction('file_manager_asset', [$this, 'getAsset'])
        ], $functions);
    }

    public function getAsset($object, string $field, string $mappingName)
    {
        return $this->fileManager->getTwigAsset($object, $field, $mappingName);
    }
}

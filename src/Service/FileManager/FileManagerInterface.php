<?php

namespace App\Service\FileManager;

use SplFileInfo;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface FileManagerInterface
{
    const TWIG_FUNCTION_PREFIX = 'getMappedAsset';

    public function __construct(
        EntityManagerInterface $entityManager,
        ParameterBagInterface $parameterBag,
        Filesystem $filesystem,
        LoggerInterface $logger
    );

    public function getUploadPath(string $mapping, bool $relative = true): ?string;

    public function getTwigFunctions(): array;

    public function uploadMappedFile(
        UploadedFile $file,
        string $mappingClass,
        bool $includePath = false
    ): string;

    public function uploadMappedFileByPath(
        string $filePath,
        string $mappingClass,
        bool $includePath = false
    ): string;

    public function copyEntityFile(SplFileInfo $file, SplFileInfo $directory): SplFileInfo;
}

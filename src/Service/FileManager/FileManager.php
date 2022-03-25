<?php

namespace App\Service\FileManager;

use RuntimeException;
use SplFileInfo;
use DateTime;
use App\Entity\UserDownload;
use App\Service\FileManager\Mapping\FileMapping;
use App\Service\FileManager\Namer\NamerInterface;
use App\Service\FileManager\Namer\UniqidNamer;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Mime\FileinfoMimeTypeGuesser;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Twig\TwigFunction;

class FileManager implements FileManagerInterface
{
    const MAPPING_PATH = 'App\\Service\\FileManager\\Mapping\\';

    private EntityManagerInterface $entityManager;
    private ParameterBagInterface $parameterBag;
    private Filesystem $filesystem;
    private LoggerInterface $logger;
    private array $parameters = [];

    public function __construct(
        EntityManagerInterface $entityManager,
        ParameterBagInterface $parameterBag,
        Filesystem $filesystem,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->parameterBag = $parameterBag;
        $this->filesystem = $filesystem;
        $this->logger = $logger;
        $this->parameters = $this->parameterBag->get('file_manager');
    }

    public function __call($methodName, $arguments)
    {
        if (strpos($methodName, self::TWIG_FUNCTION_PREFIX) === 0) {
            $partialMappingClass = str_replace(self::TWIG_FUNCTION_PREFIX, '', $methodName);

            $mappingClass = self::MAPPING_PATH . $partialMappingClass;

            if (!$this->validateMappingClass($mappingClass)) {
                $mappingClass = null;
            }

            return $this->getTwigAsset($arguments[0], $arguments[1], $mappingClass::getMappingName());
        }
        return call_user_func_array([$this, $methodName], $arguments);
    }

    private static function mbBasename(string $path): string
    {
        if (preg_match('@^.*[\\\\/]([^\\\\/]+)$@s', $path, $matches)) {
            return $matches[1];
        } else if (preg_match('@^([^\\\\/]+)$@s', $path, $matches)) {
            return $matches[1];
        }

        return '';
    }

    public static function getFullLink(string $link, ?string $host = null): string
    {
        if (strpos($link, '/') === 0) {
            $link = $host . $link;
        }

        return $link;
    }

    public function downloadUserFile(UserDownload $file): ?BinaryFileResponse
    {
        $path = $this->getServerPathFromLink($file->getLink());

        if ($this->filesystem->exists($path)) {
            return $this->returnFile($path);
        }

        $this->entityManager->remove($file);
        $this->entityManager->flush();

        return null;
    }

    public function returnFile(string $filePath): BinaryFileResponse
    {
        $filename = self::mbBasename($filePath);
        $response = new BinaryFileResponse($filePath);
        $mimeTypeGuesser = new FileinfoMimeTypeGuesser();

        if ($mimeTypeGuesser->isGuesserSupported()) {
            $response->headers->set('Content-Type', $mimeTypeGuesser->guessMimeType($filePath));
        } else {
            $response->headers->set('Content-Type', 'text/plain');
        }

        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename
        );

        return $response;
    }

    public function getServerPathFromLink(string $link): string
    {
        $publicPath = $this->parameterBag->get('kernel.project_dir') . '/public';
        return $publicPath . parse_url($link, PHP_URL_PATH);
    }

    public function uploadFile(UploadedFile $file, string $fileName, string $path = null): string
    {
        if (!$path) {
            $path = $this->parameters['path'];
        }

        try {
            $file->move($path, $fileName);
        } catch (FileException $e) {
            $this->logger->error('FileManager: ' .  $e->getMessage());
        }

        return $fileName;
    }

    public function uploadMappedFile(
        UploadedFile $file,
        string $mappingClass,
        bool $includePath = false
    ): string {
        $this->validateMappingClass($mappingClass);

        $path = $this->getUploadPath($mappingClass, false);

        if (!$namer = $mappingClass::getNamer()) {
            $namer = $this->getDefaultNamer();
        }

        $fileName = $namer->getName($file);

        $this->uploadFile($file, $fileName, $path);

        if ($includePath) {
            return $path . '/' . $fileName;
        }

        return $fileName;
    }

    public function uploadMappedFileByPath(
        string $filePath,
        string $mappingClass,
        bool $includePath = false
    ): string {
        $this->validateMappingClass($mappingClass);
        $tempFile = tempnam(sys_get_temp_dir(), 'agro');
        $binaryFile = file_get_contents($filePath);
        file_put_contents($tempFile, $binaryFile);
        $fileName = self::mbBasename($filePath);

        $file = new UploadedFile(
            $tempFile,
            $fileName,
            (new File($tempFile))->getMimeType(),
            null,
            true
        );

        return $this->uploadMappedFile(
            $file,
            $mappingClass
        );
    }

    public function getUploadPath(string $mappingClass, $relative = true): string
    {
        $uploadsDirectory = $this->parameters['directory'];
        $uploadsPath = $this->parameters['path'];
        $this->validateMappingClass($mappingClass);
        $path = $mappingClass::getPath();

        $this->filesystem->mkdir($uploadsDirectory . $path);

        if ($relative) {
           return $uploadsDirectory . $path;
        }

        return $uploadsPath . $path;
    }

    public function getTwigFunctions(): array
    {
        $mappings = $this->parameters['mappings'];
        $functions = [];

        foreach ($mappings as $mappingClass) {
            $this->validateMappingClass($mappingClass);

            $mapping = new $mappingClass();
            /** @var FileMapping $mappingClass */
            $mappingName = $mapping::getMappingName();
            $mappingClassName = $mapping::getMappingClassName();

            $functions[] = new TwigFunction($mappingName . '_asset', [$this, self::TWIG_FUNCTION_PREFIX . $mappingClassName]);
        }

        return $functions;
    }

    private function validateMappingClass(string $mappingClass): bool
    {
        $mappings = $this->parameters['mappings'];

        $mappingExist = false;
        foreach ($mappings as $mapping) {
            if ($mapping === $mappingClass) {
                $mappingExist = true;
            }
        }

        if (!$mappingExist) {
            throw new \RuntimeException(
                sprintf(
                    'FileManager: You have to add the class "%s" to the parameters file_manager.mappings',
                    $mappingClass
                )
            );
        }

        if (!class_exists($mappingClass)) {
            throw new \RuntimeException(
                sprintf(
                    'FileManager: Class "%s" does not exist.',
                    $mappingClass
                )
            );
        }

        if (!is_subclass_of($mappingClass, FileMapping::class) ) {
            throw new \RuntimeException(
                sprintf(
                    'FileManager: Class "%s" is expected to implement FileMapping.',
                    $mappingClass
                )
            );
        }

        return $mappingExist;
    }

    public function getTwigAsset($object, string $field, string $mappingName = null)
    {
        if ($mappingName) {
            $mapping = self::MAPPING_PATH . self::convertMappingNameToClassName($mappingName);
            $methodName = 'get' . ucfirst($field);

            if (is_object($object) && method_exists($object, $methodName)) {
                $fileName = $object->$methodName();

                if ($fileName) {
                    return '/' . $this->getUploadPath($mapping) . '/' . $fileName;
                }
            }
        }

        return '';
    }

    public static function convertMappingNameToClassName($mappingName) {
        $camelCaseToSnakeCaseNameConverter = new CamelCaseToSnakeCaseNameConverter();
        $mappingClass = $camelCaseToSnakeCaseNameConverter->denormalize($mappingName);
        return ucfirst($mappingClass) . 'Mapping';
    }

    public function getDefaultNamer(): NamerInterface
    {
        return new UniqidNamer();
    }
    /**
     * Copy entity file.
     *
     * @param   SplFileInfo $file           File.
     * @param   SplFileInfo $directory      Directory.
     *
     * @return  SplFileInfo                 File copy.
     * @throws  RuntimeException            Process failed.
     */
    public function copyEntityFile(SplFileInfo $file, SplFileInfo $directory): SplFileInfo
    {
        if (!$file->isFile()) {
            throw new RuntimeException("{$file->getPathname()} is not a file");
        }

        $currentTimestamp   = (new DateTime('now'))->getTimestamp();
        $fileHash           = hash_file('md5', $file->getPathname());

        while (true) {
            $newFile = new SplFileInfo(
                $directory->getPathname() . DIRECTORY_SEPARATOR .
                "$currentTimestamp-$fileHash.{$file->getExtension()}"
            );

            if ($newFile->isFile()) {
                $currentTimestamp += 1;
            } else {
                break;
            }
        }

        $copyResult = copy($file->getPathname(), $newFile->getPathname());
        if (!$copyResult) {
            throw new RuntimeException(
                "failed to copy file from {$file->getPathname()} " .
                "to {$newFile->getPathname()}"
            );
        }
        return $newFile;
    }
}

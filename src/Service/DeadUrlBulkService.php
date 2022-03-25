<?php

namespace App\Service;

use App\Entity\DeadUrl;
use Doctrine\ORM\EntityManagerInterface;

class DeadUrlBulkService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function getDeadUrlBulkOrNonActiveBulkCounterByUri(string $uri)//mixed - int null or DeadUrl
    {
        if ($uri === '/') {
            return null;
        }
        $uriParts = $this->parseUriParts($uri); //first index is 1 instead of 0
        $resultPatterns = $this->createCorrectPatternsForSearchInRepository($uri, $uriParts);

        return $this->findDeadUrlBulkByPattern($resultPatterns);

    }

    public function parseUriParts(string $uri): ?array
    {
        $uriParts = explode('/', $uri);
        return $this->unsetFirstForwardSlashFromUriParts($uriParts);
    }

    public function unsetFirstForwardSlashFromUriParts(array $uriParts): array
    {
        unset($uriParts[0]);
        return $uriParts;
    }

    public function createCorrectPatternsForSearchInRepository(string $uri, array $uriParts): array
    {
        $i = 1;
        $resultPatterns = [];

        while ($i <= count($uriParts)) {
            if ($i == 1) {
                $deadUrlPatternWithForwardSlashAndAsteriskAtEndOfString = '/' . $uriParts[$i] . '/' . '*';
            }
            if ($i == count($uriParts)) {
                $deadUrlPatternWithForwardSlashAndAsteriskAtEndOfString = $uri . '/*';
            }
            if ($i > 1 && $i < count($uriParts)) {
                $deadUrlPatternWithForwardSlashAndAsteriskAtEndOfString = substr($resultPatterns[$i - 2], 0, -2) . '/' . $uriParts[$i] . '/*';
            }
            $resultPatterns[] = $deadUrlPatternWithForwardSlashAndAsteriskAtEndOfString;
            $i++;
        }

        return $resultPatterns;
    }

    private function findDeadUrlBulkByPattern(array $patterns) //mixed
    {
        $patternMatchCounter = 0;

        $activeDeadUrlPatters = [];
        foreach ($patterns as $pattern) {
            $checkSum = crc32($pattern);
            $checkSum = current(unpack('l', pack('l', $checkSum)));
            $deadUrl = $this->em->getRepository(DeadUrl::class)->findOneBy([
                'checkSum' => $checkSum,
                'deadRequest' => $pattern
            ]);

            if ($deadUrl) {
                $patternMatchCounter++;
                $deadUrl->increaseAttemptAmount();
                $this->em->persist($deadUrl);
            }
            if ($deadUrl && $deadUrl->getIsActive()) {
                $activeDeadUrlPatters[] = $deadUrl;
            }
        }
        if ($patternMatchCounter > 0 && empty($activeDeadUrlPatters)) {
            $this->em->flush();
            return $patternMatchCounter;
        }

        return $this->getActiveParentPattern($activeDeadUrlPatters);
    }

    private function getActiveParentPattern(array $activeDeadUrlPatters):?DeadUrl
    {
        if (!empty($activeDeadUrlPatters)) {
            return $activeDeadUrlPatters[0];
        }else{
            return null;
        }
    }

    public static function getServiceAsClass(EntityManagerInterface $em)
    {
        return new static($em);
    }
}
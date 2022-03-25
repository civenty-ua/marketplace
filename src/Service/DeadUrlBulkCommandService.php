<?php

namespace App\Service;

class DeadUrlBulkCommandService extends DeadUrlBulkService
{
    public function createCorrectPatternsForSearchInRepository(string $uri, array $uriParts): array
    {
        $i = 0;
        $resultPatterns = [];

        while ($i <= (count($uriParts)-1)) {
            if ($i == 0) {
                $deadUrlPatternWithForwardSlashAndAsteriskAtEndOfString = '/' . $uriParts[$i] . '/' . '*';
            }

            if ($i > 0 && $i < (count($uriParts)-1)) {
                $deadUrlPatternWithForwardSlashAndAsteriskAtEndOfString = substr($resultPatterns[$i - 1], 0, -2) . '/' . $uriParts[$i] . '/*';
            }

            $resultPatterns[] = $deadUrlPatternWithForwardSlashAndAsteriskAtEndOfString;
            if ($i == (count($uriParts)-2)){
                break;
            }
            $i++;
        }

        return $resultPatterns;
    }
    public function parseUriParts(string $uri): ?array
    {
//        if (str_starts_with($uri,'https://blockgeeks.com')){ for fakerData
//            $uri = substr($uri,22);
//        }
        $uriParts = explode('/', $uri);
        return $this->unsetFirstForwardSlashFromUriParts($uriParts);
    }

    public function unsetFirstForwardSlashFromUriParts(array $uriParts): array
    {
        $uriPartsNew =[];
        foreach ($uriParts as  $uriPart){
            if ($uriPart !== ''){
                $uriPartsNew[] = $uriPart;
            }
        }

        return $uriPartsNew;
    }
}
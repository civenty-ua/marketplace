<?php


namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class CallStaticFunctionsExtension extends AbstractExtension
{

    public function getFilters()
    {
        return [
            new TwigFilter('static_call', [$this, 'staticCall']),
        ];
    }

    function staticCall($data) {
        if (class_exists($data['class']) && method_exists($data['class'], $data['function'])) {
            return call_user_func_array([$data['class'], $data['function']], $data['args']);
        }

        return null;
    }
}
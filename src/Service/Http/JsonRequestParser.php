<?php

declare(strict_types=1);

namespace App\Service\Http;

use Symfony\Component\HttpFoundation\Request;

final class JsonRequestParser
{
    public function parse(Request $request): array
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            throw new \InvalidArgumentException('Invalid JSON body.');
        }

        return $data;
    }
}

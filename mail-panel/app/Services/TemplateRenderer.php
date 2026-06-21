<?php

namespace App\Services;

class TemplateRenderer
{
    public function render(string $content, array $data): string
    {
        $rendered = $content;

        foreach ($data as $key => $value) {
            if (! is_scalar($value)) {
                continue;
            }

            $rendered = str_replace('{{'.$key.'}}', (string) $value, $rendered);
        }

        return $rendered;
    }
}

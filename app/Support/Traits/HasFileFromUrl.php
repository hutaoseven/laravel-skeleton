<?php

declare(strict_types=1);

namespace App\Support\Traits;

use Illuminate\Http\UploadedFile;

/**
 * @see https://github.com/MrPunyapal/basic-crud/blob/main/app/Traits/HasFileFromUrl.php
 *
 * @mixin \Illuminate\Foundation\Http\FormRequest
 * @mixin \Illuminate\Http\Request
 */
trait HasFileFromUrl
{
    public function resolveFileFromUrl(string $field): void
    {
        if (! $this->hasFile($field) && filter_var($this->get($field), FILTER_VALIDATE_URL)) {
            $file = UploadedFile::makeFromUrl(
                (string) $this->string($field)
            );

            if ($file !== null) {
                $this->merge([
                    $field => $file,
                ]);
            }
        }
    }
}

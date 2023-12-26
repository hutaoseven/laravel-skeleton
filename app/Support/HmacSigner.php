<?php

declare(strict_types=1);

namespace App\Support;

use App\Contracts\SignerContract;

class HmacSigner implements SignerContract
{
    public function __construct(private string $secret = '', private string $algo = 'sha256') {}

    public function sign(array $payload): string
    {
        return hash_hmac($this->algo, $this->toPreEncryptedData($payload), $this->secret);
    }

    public function validate(string $signature, array $payload): bool
    {
        return hash_equals($signature, $this->sign($payload));
    }

    protected function sort(array $payload): array
    {
        ksort($payload);

        foreach ($payload as &$item) {
            \is_array($item) and $item = $this->sort($item);
        }

        return $payload;
    }

    protected function toPreEncryptedData(array $payload): string
    {
        return urldecode(http_build_query($this->sort($payload)));
    }
}

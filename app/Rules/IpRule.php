<?php

namespace App\Rules;

use Symfony\Component\HttpFoundation\IpUtils;

final class IpRule extends Rule
{
    /**
     * @var string[]
     */
    protected $v4 = [
        '0.0.0.0/8',
        '10.0.0.0/8',
        '127.0.0.0/8',
        '172.16.0.0/12',
        '192.168.0.0/16',
        '169.254.0.0/16',
    ];

    /**
     * @var string[]
     */
    protected $v6 = [
        '::1/128',
        'fc00::/7',
        'fd00::/8',
        'fe80::/10',
    ];

    public function __construct(protected bool $isPublic = true) {}

    public function isV4(string $ip)
    {
        return ! $this->isV6($ip);
    }

    public function isV6(string $ip)
    {
        return substr_count($ip, ':') > 1;
    }

    public function isPrivate(string $ip)
    {
        $ips = $this->isV4($ip) ? $this->v4 : $this->v6;

        return IpUtils::checkIp($ip, $ips);
    }

    public function isPublic(string $ip)
    {
        return ! $this->isPrivate($ip);
    }

    public function passes(string $attribute, mixed $value): bool
    {
        return $this->isPublic ? $this->isPublic($value) : $this->isPrivate($value);
    }
}

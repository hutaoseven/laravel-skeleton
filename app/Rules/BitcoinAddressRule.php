<?php

namespace App\Rules;

class BitcoinAddressRule extends RegexRule
{
    /**
     * {@inheritDoc}
     */
    protected function pattern(): string
    {
        /** @lang PhpRegExp */
        return '/^(?:bc1|[13])[a-zA-HJ-NP-Z0-9]{25,39}$/';
    }
}

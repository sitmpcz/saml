<?php

namespace Sitmp\Saml;

class SamlAcs
{
    function __construct(
            public string $samlNameId,
            public string $samlNameIdFormat,
            public array $samlAttributes,
            public string $samlNameIdNameQualifier,
            public string $samlNameIdSPNameQualifier,
            public ?string $samlSessionIndex = null,
            public ?string $samlUserdata = null)
    {
    }
}
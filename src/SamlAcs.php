<?php

namespace Sitmp\Saml;

class SamlAcs
{
    function __construct(
            public ?string $samlNameId,
            public ?string $samlNameIdFormat,
            // tohle je zasadni - tady se hleda info o prihlasenem uzivateli
            public array $samlUserdata,
            public ?string $samlNameIdNameQualifier,
            public ?string $samlNameIdSPNameQualifier,
            public ?string $samlSessionIndex = null
        )
    {
    }
}
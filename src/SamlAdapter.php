<?php
namespace Sitmp\Saml;

interface SamlAdapter
{
    public function login(?string $returnTo = null): void;

    function acs($authErrorCallback,$genericErrorCallback): SamlAcs;
    public function logout(?string $returnTo, array $parameters, mixed $nameId, mixed $sessionIndex, bool $stay, mixed $nameIdFormat, mixed $samlNameIdNameQualifier, mixed $samlNameIdSPNameQualifier): void;

    public function slo($successCallback,$authErrorCallback): void;

    public function getMetadata(): string;


}
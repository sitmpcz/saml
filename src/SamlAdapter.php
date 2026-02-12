<?php
namespace Sitmp\Saml;

interface SamlAdapter
{
    /**
     * presmeruje na prihlasovaci stranku IDP
     */
    public function login(?string $returnTo = null): void;

    /**
     * Zpracuje ACS (SAMLResponse) a vrátí data o uživateli.
     * @param callable $authErrorCallback   fn(Throwable $e): void
     * @param callable $genericErrorCallback fn(Throwable $e): void
     */
    function acs(?callable $authErrorCallback = null,?callable $genericErrorCallback = null): SamlAcs;

    /**
     * Odeslání LogoutRequest na IdP
     */
    public function logout(
        ?string $returnTo = null,
        array $parameters = [],
        ?string $nameId = null,
        ?string $sessionIndex = null,
        bool $stay = false,
        ?string $nameIdFormat = null,
        ?string $nameIdNameQualifier = null,
        ?string $nameIdSPNameQualifier = null
    ): void;

    /**
     * Příjem SLO požadavku na SLS endpointu (IdP → SP)
     */
    public function slo(?callable $successCallback = null,?callable $authErrorCallback = null): void;

    /**
     * Vygeneruje SP metadata (XML)
     */
    public function getMetadata(): string;


}
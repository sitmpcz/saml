<?php
// nastrel SimpleSamlAdapteru
// musi se nacist prislsna knihovna i composerem
// composer require simplesamlphp/simplesamlphp
declare(strict_types=1);

namespace Sitmp\Saml\Adapter;

use Sitmp\Saml\SamlAcs;
use Sitmp\Saml\SamlAdapter;

final class SimpleSamlAdapter implements SamlAdapter
{
    private array $settings;
    private string $authSource;
    private \SimpleSAML\Auth\Simple $as;

    public function __construct(array $settings)
    {
        $this->settings = $settings;
        $this->authSource = 'default-sp';
        // private ?string $sloReturnTo = null
        $this->as = new \SimpleSAML\Auth\Simple($this->authSource);

    }


    public function login(?string $returnTo = null): void
    {
        throw new \Exception('Not implemented yet');
        /*$this->as->requireAuth([
            'ReturnTo' => $returnTo, // může být null – SSP si vezme current URL
        ]);*/
    }

    public function acs(?callable $authError = null, ?callable $genericError = null): SamlAcs
    {
        throw new \Exception('Not implemented yet');
        /*
        // U SimpleSAML se ACS typicky odbaví jeho endpointem.
        // Po návratu do aplikace už jen čteme stav/session:
        try {
            if (!$this->as->isAuthenticated()) {
                throw new \Exception('Not authenticated (no SAML session)');
            }
            $attrs = $this->as->getAttributes(); // [name => [values…]]
            $nameId  = $this->as->getAuthData('saml:sp:NameID')['Value'] ?? null;
            $format  = $this->as->getAuthData('saml:sp:NameID')['Format'] ?? null;
            $nq      = $this->as->getAuthData('saml:sp:NameID')['NameQualifier'] ?? null;
            $spnq    = $this->as->getAuthData('saml:sp:NameID')['SPNameQualifier'] ?? null;
            $sessIdx = $this->as->getAuthData('saml:sp:SessionIndex') ?? null;

            return new SamlAcs(
                $nameId,
                $format,
                $attrs,
                $nq,
                $spnq,
                $sessIdx
            );
        } catch (\Throwable $e) {
            // SimpleSAML už řešil podpisy/bindingy – tady jen vrátíme chybu volajícímu
            if ($authError !== null) {
                $authError($e);
            }
            throw $e;
        }*/
    }

    public function logout(
        ?string $returnTo = null,
        array $parameters = [],
        ?string $nameId = null,
        ?string $sessionIndex = null,
        bool $stay = false,
        ?string $nameIdFormat = null,
        ?string $nameIdNameQualifier = null,
        ?string $nameIdSPNameQualifier = null
    ): void {
        throw new \Exception('Not implemented yet');
        /*
        // SimpleSAML si NameID/SessionIndex vytáhne ze své session sám
        $this->as->logout($returnTo ?? $this->sloReturnTo);
        // pokud se sem kód vrátí, logout je lokálně hotový (SLO flow může přesměrovat)*/
    }

    public function slo(?callable $success = null, ?callable $authError = null): void
    {
        throw new \Exception('Not implemented yet');
        // SLS endpoint obsluhuje SimpleSAML. Tady typicky není co dělat,
        // ale kvůli kontraktu můžeme jen "dopsat" úspěch (jsme po návratu).
        /*try {
            $success();
        } catch (\Throwable $e) {
            if ($authError !== null) {
                $authError($e);
            }
            //throw $e;
        }*/
    }

    public function getMetadata(): string
    {
        throw new \Exception('Not implemented yet');
        /*
        // vyrobíme SP metadata programově (alternativa k SSP /metadata endpointu)
        $entityId = \SimpleSAML\Configuration::getInstance()
            ->getString('entityid', null); // můžeš číst i z authsources.php

        $cfg = \SimpleSAML\Configuration::getInstance();
        $metab = new \SimpleSAML\Metadata\SAMLBuilder($entityId);

        $sp = [
            'AssertionConsumerService' => [
                ['Binding' => \SAML2\Constants::BINDING_HTTP_POST, 'Location' => $cfg->getString('assertionconsumerservice', null)],
            ],
            'SingleLogoutService' => [
                ['Binding' => \SAML2\Constants::BINDING_HTTP_POST,    'Location' => $cfg->getString('singlelogoutservice', null)],
                ['Binding' => \SAML2\Constants::BINDING_HTTP_REDIRECT, 'Location' => $cfg->getString('singlelogoutservice', null)],
            ],
            // volitelně: NameIDFormat, KeyDescriptor, …
        ];

        $metab->addSPSSODescriptor($sp);
        return $metab->getEntityDescriptor()->ownerDocument->saveXML();
        */
    }
}
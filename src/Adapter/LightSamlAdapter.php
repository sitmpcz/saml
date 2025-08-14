<?php
// pokud by se pripravovala varianta pro litesaml (nebo treba simplesaml),
// oba by meli umel back channel logout
// musi se nacist prislsna knihovna i composerem
// composer require litesaml/lightsaml symfony/http-foundation psr/log

declare(strict_types=1);

namespace Sitmp\Saml\Adapter;

use Sitmp\Saml\SamlAcs;
use Sitmp\Saml\SamlAdapter;
/*
use LightSaml\Binding\HttpPostBinding;
use LightSaml\Binding\HttpRedirectBinding;
use LightSaml\Builder\EntityDescriptor\SimpleEntityDescriptorBuilder;
use LightSaml\Context\Profile\MessageContext;
use LightSaml\Credential\KeyHelper;
use LightSaml\Credential\X509Certificate;
use LightSaml\Helper;
use LightSaml\Model\Assertion\Issuer;
use LightSaml\Model\Metadata\SingleLogoutService;
use LightSaml\Model\Protocol\AuthnRequest;
use LightSaml\Model\Protocol\LogoutRequest;
use LightSaml\Model\Protocol\LogoutResponse;
use LightSaml\Model\Protocol\Response as SamlResponse;
use LightSaml\Model\XmlDSig\SignatureWriter;
use LightSaml\SamlConstants;
use LightSaml\Validator\Model\Signature\SignatureValidator;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
*/

final class LightSamlAdapter implements SamlAdapter
{
    private array $settings;

    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }



    public function login(?string $returnTo = null): void
    {
        throw new \Exception('Not implemented yet');
        /*
        [$spCert, $spKey] = $this->loadSpKeyPair();

        $authn = (new AuthnRequest())
            ->setID(Helper::generateID())
            ->setIssueInstant(new \DateTimeImmutable())
            ->setDestination($this->config->idpSsoUrl)
            // očekávaný binding odpovědi IdP → ACS (většinou POST)
            ->setProtocolBinding(SamlConstants::BINDING_SAML2_HTTP_POST)
            ->setAssertionConsumerServiceURL($this->config->acsUrl)
            ->setIssuer(new Issuer($this->config->spEntityId));

        if (!empty($this->config->nameIdFormat)) {
            $authn->setNameIDPolicy(
                (new \LightSaml\Model\Protocol\NameIDPolicy())
                    ->setFormat($this->config->nameIdFormat)
                    ->setAllowCreate(true)
            );
        }

        if ($spKey && $spCert) {
            $authn->setSignature(new SignatureWriter($spKey, $spCert));
        }

        $binding = new HttpRedirectBinding();
        $resp = new Response();
        $binding->send($authn, $resp, $this->config->idpSsoUrl, [
            'RelayState' => (string)($returnTo ?? ''),
        ]);

        $this->emit($resp);
        */
    }

    function acs(?callable $authErrorCallback = null,?callable $genericErrorCallback = null): SamlAcs
    {
        throw new \Exception('Not implemented');
        /*try {
            $httpReq = Request::createFromGlobals();

            // SAMLResponse typicky dorazí POSTem
            $ctx = new MessageContext();
            (new HttpPostBinding())->receive($httpReq, $ctx);


            $resp = $ctx->getMessage();

            // 1) Validace podpisu proti IdP certifikátu
            $idpCert = X509Certificate::fromFile($this->config->idpCert);
            (new SignatureValidator())->validate($resp, [$idpCert]);

            // 2) Kontroly issuer/audience/destination (doporučujeme ponechat)
            $issuer = (string)$resp->getIssuer();
            if ($issuer === '') {
                throw new \RuntimeException('SAML Response missing Issuer');
            }
            $destination = (string)$resp->getDestination();
            if ($destination && rtrim($destination, '/') !== rtrim($this->config->acsUrl, '/')) {
                throw new \RuntimeException('SAML Response Destination mismatch');
            }

            $assertion = $resp->getFirstAssertion();
            if (!$assertion) {
                throw new \RuntimeException('No Assertion in SAML Response');
            }

            // 3) Extrakce údajů
            $nameIdObj = $assertion->getSubject()?->getNameID();
            $nameId = $nameIdObj?->getValue();
            $nameIdFormat = $nameIdObj?->getFormat();
            $nameIdNameQualifier = $nameIdObj?->getNameQualifier();
            $nameIdSPNameQualifier = $nameIdObj?->getSPNameQualifier();
            $sessionIndex = $assertion->getFirstAuthnStatement()?->getSessionIndex();

            $attrs = [];
            $attrStmt = $assertion->getFirstAttributeStatement();
            if ($attrStmt) {
                foreach ($attrStmt->getAllAttributes() as $a) {
                    $attrs[$a->getName()] = $a->getAllAttributeValues();
                }
            }

            // 5) DTO pro volající vrstvu
            return new SamlAcs(
                $nameId,
                $nameIdFormat,
                $attrs,
                $nameIdNameQualifier,
                $nameIdSPNameQualifier,
                $sessionIndex,
            );
        } catch (\Throwable $e) {
            // „auth“ chyby pošli auth callbacku, ostatní generic
            $authErrors = [
                'Signature', 'Issuer', 'Assertion', 'Audience', 'Destination', 'NotOnOrAfter', 'NotBefore'
            ];
            $msg = $e->getMessage();
            if (array_reduce($authErrors, fn($c,$k)=>$c||str_contains($msg,$k), false)) {
                $authErrorCallback($e);
            } else {
                $genericErrorCallback($e);
            }
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
    ): void
    {
        throw new \Exception('not implemented yet');
        /*
       [$spCert, $spKey] = $this->loadSpKeyPair();

        $req = (new LogoutRequest())
            ->setID(Helper::generateID())
            ->setIssueInstant(new \DateTimeImmutable())
            ->setDestination($this->config->idpSloUrl)
            ->setIssuer(new Issuer($this->config->spEntityId));

        if ($nameId) {
            $name = (new \LightSaml\Model\Assertion\NameID())
                ->setValue($nameId)
                ->setFormat($nameIdFormat ?: null)
                ->setNameQualifier($nameIdNameQualifier ?: null)
                ->setSPNameQualifier($nameIdSPNameQualifier ?: null);
            $req->setNameID($name);
        }
        if ($sessionIndex) {
            $req->setSessionIndex($sessionIndex);
        }
        if ($spKey && $spCert) {
            $req->setSignature(new SignatureWriter($spKey, $spCert));
        }

        $binding = new HttpRedirectBinding();
        $resp = new Response();
        $relay = $parameters['RelayState'] ?? (string)($returnTo ?? '');
        $binding->send($req, $resp, $this->config->idpSloUrl, ['RelayState' => $relay]);

        $this->emit($resp);
        */
    }

    public function slo(?callable $successCallback = null,?callable $authErrorCallback = null): void
    {
        throw new \Exception('not implemented yet');
        /*
    try {
            $httpReq = Request::createFromGlobals();

            $ctx = new MessageContext();
            if ($httpReq->isMethod('POST') && ($httpReq->request->has('SAMLRequest') || $httpReq->request->has('SAMLResponse'))) {
                (new HttpPostBinding())->receive($httpReq, $ctx);
            } else {
                (new HttpRedirectBinding())->receive($httpReq, $ctx);
            }

            $msg = $ctx->getMessage();

            // validace podpisu
            $idpCert = X509Certificate::fromFile($this->config->idpCert);
            (new SignatureValidator())->validate($msg, [$idpCert]);

            if ($msg instanceof LogoutRequest) {
                // ukonči lokální session
                $this->session['saml.auth'] = false;
                $this->session['saml.nameId'] = null;
                $this->session['saml.sessionIndex'] = null;
                $this->session['saml.attrs'] = [];

                // pošli SUCCESS odpověď zpět IdP
                $resp = (new LogoutResponse())
                    ->setID(Helper::generateID())
                    ->setInResponseTo($msg->getID())
                    ->setIssueInstant(new \DateTimeImmutable())
                    ->setDestination($this->config->idpSloUrl)
                    ->setStatus(new \LightSaml\Model\Protocol\Status(
                        new \LightSaml\Model\Protocol\StatusCode(SamlConstants::STATUS_SUCCESS)
                    ));

                (new HttpRedirectBinding())->send($resp, new Response(), $this->config->idpSloUrl, [
                    'RelayState' => (string)($httpReq->get('RelayState') ?? ''),
                ]);

                $successCallback();
                return;
            }

            if ($msg instanceof LogoutResponse) {
                // dokončeno – přesměruj na RelayState (pokud je)
                $returnTo = (string)($httpReq->get('RelayState') ?? '');
                if ($returnTo !== '') {
                    header('Location: '.$returnTo);
                    exit;
                }
                $successCallback();
                return;
            }

            throw new \RuntimeException('Unsupported SLO message');
        } catch (\Throwable $e) {
            ($this->logger?->error('SAML SLO failed: '.$e->getMessage())) ?? null;
            $authErrorCallback($e);
            throw $e;
        }
         */
    }

    public function getMetadata(): string
    {
        throw new \Exception('not implemented yet');
        /*
     $cert = is_string($this->config->spCert ?? null) && $this->config->spCert !== ''
            ? X509Certificate::fromFile($this->config->spCert)
            : null;

        $builder = new SimpleEntityDescriptorBuilder(
            $this->config->spEntityId,
            $this->config->acsUrl,
            $cert
        );

        $entity = $builder->get();
        $sp = $entity->getFirstSpSsoDescriptor();

        // Publikuj oba SLO bindingy na stejné SLS URL
        $sp->addSingleLogoutService(new SingleLogoutService(
            SamlConstants::BINDING_SAML2_HTTP_POST,
            $this->config->slsUrl
        ));
        $sp->addSingleLogoutService(new SingleLogoutService(
            SamlConstants::BINDING_SAML2_HTTP_REDIRECT,
            $this->config->slsUrl
        ));

        if (!empty($this->config->nameIdFormat)) {
            $sp->addNameIDFormat($this->config->nameIdFormat);
        }

        $xml = $entity->toXml()->ownerDocument->saveXML();
        return (string)$xml;
        */
    }

    /*private function outputAndExit(Response $response): void
    {
        // v Nette to převeď na IResponse, tady pro POC pošlu hlavičky a umřu
        foreach ($response->headers->allPreserveCase() as $k => $vals) {
            foreach ($vals as $v) header("$k: $v", false);
        }
        http_response_code($response->getStatusCode());
        echo $response->getContent();
        exit;
    }*/

    /* =========================
    Helpers
    ========================= */

    /** @return array{0:?X509Certificate,1:? \LightSaml\Credential\PrivateKey} */
    /*private function loadSpKeyPair(): array
    {
        $spCert = null;
        $spKey = null;

        if (!empty($this->config->spCert)) {
            $spCert = X509Certificate::fromFile($this->config->spCert);
        }
        if (!empty($this->config->spKey)) {
            $spKey = KeyHelper::createPrivateKey($this->config->spKey, null, true);
        }
        return [$spCert, $spKey];
    }

    private function emit(Response $response): void
    {
        foreach ($response->headers->allPreserveCase() as $name => $values) {
            foreach ($values as $v) {
                header($name . ': ' . $v, false);
            }
        }
        http_response_code($response->getStatusCode());
        echo $response->getContent();
        exit;
    }*/
}


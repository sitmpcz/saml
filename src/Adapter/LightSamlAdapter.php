<?php
// pokud by se pripravovala varianta pro litesaml (nebo treba simplesaml),
// oba by meli umel back channel logout
// musi se nacist prislsna knihovna i composerem

namespace Sitmp\Saml\Adapter;

use Sitmp\Saml\SamlAcs;
use Sitmp\Saml\SamlAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

//use LightSaml\Model\Protocol\AuthnRequest;
//use LightSaml\Model\Protocol\LogoutRequest;
//use LightSaml\Model\Assertion\Issuer;
//use LightSaml\SamlConstants;
//use LightSaml\Helper;
//use LightSaml\Binding\HttpRedirectBinding;
//use LightSaml\Binding\HttpPostBinding;
//use LightSaml\Context\Profile\MessageContext;


final class LightSamlAdapter implements SamlAdapter
{
    //private Config $cfg;
    //private \SessionHandlerInterface|\SessionIdInterface|\ArrayAccess $session // z Nette: \Nette\Http\SessionSection


    public function __construct(array $settings)
    {

    }



    public function login(?string $returnTo = null): void
    {
        throw new \Exception('Not implemented yet');
        /*[$spCert, $spKey] = KeyLoader::loadSpKeyPair($this->cfg->spCertFile, $this->cfg->spKeyFile);

        $authn = (new AuthnRequest())
            ->setID(Helper::generateID())
            ->setIssueInstant(new \DateTimeImmutable())
            ->setDestination($this->cfg->idpSsoUrl)
            ->setProtocolBinding(SamlConstants::BINDING_SAML2_HTTP_POST) // očekávaný binding odpovědi
            ->setAssertionConsumerServiceURL($this->cfg->acsUrl)
            ->setIssuer(new Issuer($this->cfg->spEntityId));

        if ($this->cfg->nameIdFormat) {
            $authn->setNameIDPolicy((new \LightSaml\Model\Protocol\NameIDPolicy())
                ->setFormat($this->cfg->nameIdFormat)
                ->setAllowCreate(true));
        }

        if ($spKey && $spCert) {
            $authn->setSignature(new \LightSaml\Model\XmlDSig\SignatureWriter($spKey, $spCert));
        }

        // Redirect binding pro odeslání AuthnRequest na IdP
        $binding = new HttpRedirectBinding();
        $symfonyResponse = new Response();
        $binding->send($authn, $symfonyResponse, $this->cfg->idpSsoUrl, ['RelayState' => $returnTo ?? '']);
        $this->outputAndExit($symfonyResponse);*/
    }

    function acs($authErrorCallback,$genericErrorCallback): SamlAcs
    {
        throw new \Exception('Not implemented');
    }

    public function logout(?string $returnTo, array $parameters, mixed $nameId, mixed $sessionIndex, bool $stay, mixed $nameIdFormat, mixed $samlNameIdNameQualifier, mixed $samlNameIdSPNameQualifier): void
    {
        throw new \Exception('not implemented yet');
        /*
        $req = (new LogoutRequest())
            ->setID(Helper::generateID())
            ->setIssueInstant(new \DateTimeImmutable())
            ->setDestination($this->cfg->idpSloUrl)
            ->setIssuer(new Issuer($this->cfg->spEntityId))
            ->setNameID((new \LightSaml\Model\Assertion\NameID())->setValue((string)$this->getNameId()));

        $binding = new HttpRedirectBinding();
        $resp = new Response();
        $binding->send($req, $resp, $this->cfg->idpSloUrl, ['RelayState' => $returnTo ?? '']);
        $this->outputAndExit($resp);
        */
    }

    public function slo($successCallback,$authErrorCallback): void
    {
        throw new \Exception('not implemented yet');
    }

    public function getMetadata(): string
    {
        throw new \Exception('not implemented yet');
    }

    private function outputAndExit(Response $response): void
    {
        // v Nette to převeď na IResponse, tady pro POC pošlu hlavičky a umřu
        foreach ($response->headers->allPreserveCase() as $k => $vals) {
            foreach ($vals as $v) header("$k: $v", false);
        }
        http_response_code($response->getStatusCode());
        echo $response->getContent();
        exit;
    }
}


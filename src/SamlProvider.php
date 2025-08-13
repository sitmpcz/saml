<?php
declare(strict_types=1);

namespace Sitmp\Saml;


use Nette\Http\Request;
//use Nette\Http\Response;
use Nette\SmartObject;
use Sitmp\Saml\Adapter\LightSamlAdapter;
use Sitmp\Saml\Adapter\OneLoginAdapter;

//use OneLogin\Saml2\Auth;
//use OneLogin\Saml2\Settings;
//use Tracy\Debugger;


class SamlProvider
{

    use SmartObject;


    /** @var string */
    private $x509_IdP_key;

    /** @var string */
    private $x509_SP_key;

    /** @var string */
    private $private_key;

    /** @var string */
    private $url_idp_sign_in;

    /** @var string */
    private $url_idp_sign_out;

    /** @var string */
    private $url_idp;

    /** @var string */
    private $url;

    /** @var string */
    private $backlink;

    /** @var array */
    private $extended_config;

    /** @var bool */
    private $no_publish_slo_url;

    private string $library;
    private ?SamlAdapter $adapter;

    public function __construct(array $config, Request $url)
    {
        $this->x509_IdP_key = $config['public_IdP_key'];
        $this->x509_SP_key = $config['public_SP_key'];
        $this->private_key = $config['private_SP_key'];
        $this->url_idp = $config['url_idp'];
        $this->url_idp_sign_in = $config['url_idp_sign_in'];
        $this->url_idp_sign_out = $config['url_idp_sign_out'];
        $this->backlink = $config['backlink'];
        $this->url = $url->getUrl()->getHostUrl();
        if (!isset($config['saml_force_http']) || (isset($config['saml_force_http']) && $config['saml_force_http'] === false)) {
            $this->url = str_replace('http', 'https', $this->url);
        }
        $this->extended_config =  $config['extended_config'];
        $this->no_publish_slo_url = $config['no_publish_slo_url'];
        $this->library = $config['library'];
    }

    public function getBacklink(): string
    {
        return $this->backlink;
    }

    public function getSettingsInfo(): array
    {
        $retval =  array('strict' => true, 'debug' => false,
            'sp' => array(
                'entityId' => $this->url . '/saml/metadata',
                'assertionConsumerService' => array(
                    'url' => $this->url . '/saml/acs',
                ),
                'singleLogoutService' => array(
                    'url' => $this->url . '/saml/sls',
                ),
                'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress',
                'x509cert' => $this->x509_SP_key,
                'privateKey' => $this->private_key
            ),
            'idp' => array(
                'entityId' => $this->url_idp,
                'singleSignOnService' => array(
                    'url' => $this->url_idp_sign_in,
                ),
                'singleLogoutService' => array(
                    'url' => $this->url_idp_sign_out,
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ),
                'x509cert' => $this->x509_IdP_key,
            ),
            'security' => array(
                'authnRequestsSigned' => true,
                'logoutRequestSigned' => true,
                'signMetadata' => true,
                'wantMessagesSigned' => true,
                'wantAssertionsEncrypted' => true,
                'wantAssertionsSigned' => true,
                'signatureAlgorithm' => 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha384',
                'digestAlgorithm' => 'http://www.w3.org/2001/04/xmldsig-more#sha384',
            ),
            'contactPerson' => array (
                'technical' => array (
                    'givenName' => 'Jakub Belka',
                    'emailAddress' => 'belka@plzen.eu'
                ),
                'support' => array (
                    'givenName' => 'Helpdesk SITMP',
                    'emailAddress' => 'helpdesk@plzen.eu'
                )
            )
        );
        // developer can disable to publish singleLogoutService url to metadata
        if ($this->no_publish_slo_url) {
            unset($retval['sp']['singleLogoutService']);
        }
        //return array_merge($retval, $this->extended_config);
        return array_merge_recursive($retval, $this->extended_config);
    }

    // -----------------------------------------------------------------------------------
    // implementace jednotlivych saml metod
    // -----------------------------------------------------------------------------------


    // nacterni adapteru, ktery bude realizovat jednotlive operace
    private function getAdapter() {
        if (!$this->adapter) {
            if ($this->library === "litesaml") {
                $this->adapter = new LightSamlAdapter($this->getSettingsInfo());
            } else {
                $this->adapter = new OneLoginAdapter($this->getSettingsInfo());
            }
        }
        return $this->adapter;
    }

    // prihlaseni
    public function login(?string $backlinkUrl): void
    {
        $this->getAdapter()->login($backlinkUrl);
    }

    // acs
    // paremetry jsou callbacky presenteru, pro
    // 1. zpracovani chyby prihlaseni
    // 2. zpracovani obecne chyby
    public function acs($authErrorCallback,$genericErrorCallback): SamlAcs
    {
      return $this->getAdapter()->acs($authErrorCallback,$genericErrorCallback);
    }

    public function logout(?string $returnTo, array $parameters, mixed $nameId, mixed $sessionIndex, bool $stay, mixed $nameIdFormat, mixed $samlNameIdNameQualifier, mixed $samlNameIdSPNameQualifier)
    {
        $this->getAdapter()->logout($returnTo, $parameters, $nameId, $sessionIndex, $stay, $nameIdFormat, $samlNameIdNameQualifier, $samlNameIdSPNameQualifier);
    }

    public function slo($successCallback,$authErrorCallback): void
    {
        $this->getAdapter()->slo($successCallback,$authErrorCallback);
    }


}

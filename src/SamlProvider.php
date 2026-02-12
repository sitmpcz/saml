<?php
declare(strict_types=1);

namespace Sitmp\Saml;

use Nette;
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
    private string $x509_IdP_key;

    /** @var string */
    private string $x509_SP_key;

    /** @var string */
    private string $private_key;

    /** @var string */
    private string $url_idp_sign_in;

    /** @var string */
    private string $url_idp_sign_out;

    /** @var string */
    private string $url_idp;

    /** @var string */
    private string $url;

    /** @var string */
    private string $backlink;

    /** @var array */
    private array $extended_config;

    /** @var bool */
    private bool $no_publish_slo_url;

    /** @var string */
    private string $library;

    /** @var ?SamlAdapter */
    private ?SamlAdapter $adapter = null;

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
                //'wantXMLValidation' => false, //Keycloak 26.5 assertion encryption parser error

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
            if ($this->library === "onelogin") {
                if (!class_exists(\OneLogin\Saml2\Auth::class)) {
                    throw new Nette\DI\InvalidConfigurationException("OneLogin PHP-SAML library is not installed");
                }
                $this->adapter = new OneLoginAdapter($this->getSettingsInfo());
            } else if ($this->library === "litesaml") {
                if (!class_exists(\LightSaml\SamlConstants::class)) {
                    throw new Nette\DI\InvalidConfigurationException("LightSAML library is not installed.");
                }
                if (!class_exists(\Symfony\Component\HttpFoundation\Request::class)) {
                    throw new Nette\DI\InvalidConfigurationException("LightSAML requires symfony/http-foundation");
                }
                $this->adapter = new LightSamlAdapter($this->getSettingsInfo());
            } else if ($this->library === "simplesaml") {
                if (!class_exists(\SimpleSAML\Auth\Simple::class)) {
                    throw new Nette\DI\InvalidConfigurationException("SimpleSAML library is not installed.");
                }
                $this->adapter = new SimpleSamlAdapter($this->getSettingsInfo());
            } else {
                throw new Nette\DI\InvalidConfigurationException("Unsupported SAML adapter");
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
    public function acs(?callable $authErrorCallback = null,?callable $genericErrorCallback = null): SamlAcs
    {
      return $this->getAdapter()->acs($authErrorCallback,$genericErrorCallback);
    }

    public function logout(
        ?string $returnTo = null,
        array $parameters = [],
        ?string $nameId = null,
        ?string $sessionIndex = null,
        bool $stay = false,
        ?string $nameIdFormat = null,
        ?string $samlNameIdNameQualifier = null,
        ?string $samlNameIdSPNameQualifier = null
    ): void
    {
        $this->getAdapter()->logout($returnTo, $parameters, $nameId, $sessionIndex, $stay, $nameIdFormat, $samlNameIdNameQualifier, $samlNameIdSPNameQualifier);
    }

    public function slo($successCallback,$authErrorCallback): void
    {
        $this->getAdapter()->slo($successCallback,$authErrorCallback);
    }


    public function getMetadata(): string
    {
        return $this->getAdapter()->getMetadata();
    }


}

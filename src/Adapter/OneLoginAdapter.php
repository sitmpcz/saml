<?php
// OneLogin PHP Saml adapter
// https://github.com/SAML-Toolkits/php-saml
//composer require onelogin/php-saml

namespace Sitmp\Saml\Adapter;

use OneLogin\Saml2\Auth;
use OneLogin\Saml2\Settings;
use OneLogin\Saml2\Utils;
use OneLogin\Saml2\ValidationError;
use Sitmp\Saml\SamlAcs;
use Sitmp\Saml\SamlAdapter;

final class OneLoginAdapter implements SamlAdapter
{
    private array $settings;
    private ?Auth $auth = null;

    public function __construct(array $settings)
    {
        $this->settings = $settings;
        Utils::setProxyVars(true);
    }

    private function getAuth(): Auth
    {
        if (!$this->auth) $this->auth = new Auth($this->settings);
        return $this->auth;
    }

    public function login(?string $returnTo = null): void
    {
        $this->getAuth()->login($returnTo);
    }

    public function acs(?callable $authErrorCallback = null,?callable $genericErrorCallback = null): SamlAcs
    {
        if (isset($_SESSION) && isset($_SESSION['AuthNRequestID'])) {
            $requestID = $_SESSION['AuthNRequestID'];
        } else {
            $requestID = null;
        }
        try {
            $this->getAuth()->processResponse($requestID);
        } catch (ValidationError $e) {
            //$this->handleAuthError($e);
            if ($authErrorCallback) {
                $authErrorCallback($e);
            }
        } catch (\Exception $e) {
            // for example unsupported method
            //$this->handleGenericError($e);
            if ($genericErrorCallback) {
                $genericErrorCallback($e);
            }

        }

        $errors = $this->getAuth()->getErrors();
        if ((!empty($errors)) and ($authErrorCallback)) {
            /*echo '<p>', implode(', ', $errors), '</p>';
            if ($auth->getSettings()->isDebugActive()) {
                echo '<p>' . $auth->getLastErrorReason() . '</p>';
            }*/
            $errorMessage = implode(', ', $errors);
            if ($this->getAuth()->getSettings()->isDebugActive()) {
                $errorMessage = $this->getAuth()->getLastErrorReason();
            }
            $authErrorCallback(new \Exception($errorMessage));
        }

        if (!$this->getAuth()->isAuthenticated()) {
            //echo "<p>Not authenticated</p>";
            //exit();
            //$this->handleAuthError(new \Exception("Not authenticated"));
            if ($authErrorCallback) {
                $authErrorCallback(new \Exception("Not authenticated"));
            }
        }
        return new SamlAcs(
            $this->getAuth()->getNameId(),
            $this->getAuth()->getNameIdFormat(),
            // getAttributes se plni do samlUserdata
            $this->getAuth()->getAttributes(),
            $this->getAuth()->getNameIdNameQualifier(),
            $this->getAuth()->getNameIdSPNameQualifier(),
            $this->getAuth()->getSessionIndex()
        );
        //$saml = $this->getSession()->getSection('saml');
        //$saml->samlUserdata = $auth->getAttributes();
        //$saml->samlNameId = $auth->getNameId();
        //$saml->samlNameIdFormat = $auth->getNameIdFormat();
        //$saml->samlNameIdNameQualifier = $auth->getNameIdNameQualifier();
        //$saml->samlNameIdSPNameQualifier = $auth->getNameIdSPNameQualifier();
        //$saml->samlSessionIndex = $auth->getSessionIndex();
        //$saml->backlinkUrl = $this->getHttpRequest()->getPost('RelayState');
    }

    //public function logout(?string $returnTo, array $parameters, mixed $nameId, mixed $sessionIndex, bool $stay, mixed $nameIdFormat, mixed $samlNameIdNameQualifier, mixed $samlNameIdSPNameQualifier): void
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
        $this->getAuth()->logout($returnTo, $parameters, $nameId, $sessionIndex, $stay, $nameIdFormat, $nameIdNameQualifier, $nameIdSPNameQualifier);
    }

    public function slo(?callable $successCallback = null,?callable $authErrorCallback = null): void
    {
        //$auth = new Auth($this->samlProvider->getSettingsInfo());
        if (isset($_SESSION) && isset($_SESSION['LogoutRequestID'])) {
            $requestID = $_SESSION['LogoutRequestID'];
        } else {
            $requestID = null;
        }

        $this->getAuth()->processSLO(
            false,
            $requestID,
            false,
            $successCallback
        );
        $errors = $this->getAuth()->getErrors();
        //if ((empty($errors)) and ($successCallback)) {
            //$successCallback();
        //} else {
        if (!empty($errors)) {
            //echo '<p>', implode(', ', $errors), '</p>';
            //if ($auth->getSettings()->isDebugActive()) {
            //    echo '<p>' . $auth->getLastErrorReason() . '</p>';
            //}
            if ($authErrorCallback) {
                $errorMessage = implode(', ', $errors);
                if ($this->getAuth()->getSettings()->isDebugActive()) $errorMessage .= " - ".$this->getAuth()->getLastErrorReason();
                $authErrorCallback(new \Exception($errorMessage));
            }
        }
        // if isset RelayState - redirect to
        $relayState = $_GET['RelayState'] ?? null;
        if ($relayState && Utils::getSelfURL() !== $relayState) {
            $this->getAuth()->redirectTo($relayState);
            exit;
        }
        //echo 'SLO done';
    }

    public function getMetadata(): string
    {
        $settingsObj = new Settings($this->settings, true);
        $metadata = $settingsObj->getSPMetadata();
        $errors = $settingsObj->validateMetadata($metadata);
        if (empty($errors)) {
            //$this->getHttpResponse()->setHeader('Content-Type', 'text/xml');
            return $metadata;
        } else {
            throw new \OneLogin\Saml2\Error(
                'Invalid SP metadata: ' . implode(', ', $errors),
                \OneLogin\Saml2\Error::METADATA_SP_INVALID
            );
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use OneLogin\Saml2\Auth;
use OneLogin\Saml2\Settings;
use OneLogin\Saml2\Utils;

class SamlPresenter extends Nette\Application\UI\Presenter
{
    /** @var \Sitmp\Saml\SamlProvider @inject */
    public $samlProvider;

    public function __construct()
    {
        Utils::setProxyVars(true);
    }

    // pokud je jako parametr zadan backink, poslu ho i do saml jako RelayState
    public function actionDefault(?string $backlink = null): void
    {
        $auth = new Auth($this->samlProvider->getSettingsInfo());
        $backlinkUrl = null;
        if ($backlink) $backlinkUrl = $this->backlink2Url($backlink);
        $auth->login($backlinkUrl);

    }

    // convert backlink to url
    private function backlink2Url(string $key): ?string
    {
        $session = $this->getSession('Nette.Application/requests');
        if (!isset($session[$key]) || ($session[$key][0] !== null && $session[$key][0] !== $this->getUser()->getId())) {
            return null;
        }
        $request = clone $session[$key][1];
        unset($session[$key]);
        $params = $request->getParameters();
        $request->setParameters($params);
        $params = $request->getParameters();
        unset($params[self::ACTION_KEY], $params[self::PRESENTER_KEY]);
        return $request->getUrl().urldecode(http_build_query($params));
    }

    public function actionAcs(): void
    {
        $auth = new Auth($this->samlProvider->getSettingsInfo());
        if (isset($_SESSION) && isset($_SESSION['AuthNRequestID'])) {
            $requestID = $_SESSION['AuthNRequestID'];
        } else {
            $requestID = null;
        }
        $auth->processResponse($requestID);

        $errors = $auth->getErrors();

        if (!empty($errors)) {
            echo '<p>', implode(', ', $errors), '</p>';
            if ($auth->getSettings()->isDebugActive()) {
                echo '<p>' . $auth->getLastErrorReason() . '</p>';
            }
        }

        if (!$auth->isAuthenticated()) {
            echo "<p>Not authenticated</p>";
            exit();
        }
        $saml = $this->getSession()->getSection('saml');
        $saml->samlUserdata = $auth->getAttributes();
        $saml->samlNameId = $auth->getNameId();
        $saml->samlNameIdFormat = $auth->getNameIdFormat();
        $saml->samlNameIdNameQualifier = $auth->getNameIdNameQualifier();
        $saml->samlNameIdSPNameQualifier = $auth->getNameIdSPNameQualifier();
        $saml->samlSessionIndex = $auth->getSessionIndex();
        //  if (isset($_POST['RelayState']) && Utils::getSelfURL() != $_POST['RelayState']) {
        //    $this->redirect($this->samlProvider->getBacklink());
        // }
        // relayState is saved in session backlinkUrl (check self URL !!!)
        $saml->backlinkUrl = $this->getHttpRequest->getPost('RelayState');
    }

    public function actionLogout(): void
    {
        $auth = new Auth($this->samlProvider->getSettingsInfo());
        $returnTo = null;
        $parameters = array();
        $nameId = null;
        $sessionIndex = null;
        $nameIdFormat = null;
        $samlNameIdNameQualifier = null;
        $samlNameIdSPNameQualifier = null;

        if (isset($_SESSION['samlNameId'])) {
            $nameId = $_SESSION['samlNameId'];
        }
        if (isset($_SESSION['samlNameIdFormat'])) {
            $nameIdFormat = $_SESSION['samlNameIdFormat'];
        }
        if (isset($_SESSION['samlNameIdNameQualifier'])) {
            $samlNameIdNameQualifier = $_SESSION['samlNameIdNameQualifier'];
        }
        if (isset($_SESSION['samlNameIdSPNameQualifier'])) {
            $samlNameIdSPNameQualifier = $_SESSION['samlNameIdSPNameQualifier'];
        }
        if (isset($_SESSION['samlSessionIndex'])) {
            $sessionIndex = $_SESSION['samlSessionIndex'];
        }

        $auth->logout($returnTo, $parameters, $nameId, $sessionIndex, false, $nameIdFormat, $samlNameIdNameQualifier, $samlNameIdSPNameQualifier);
    }

    public function actionSls(): void
    {
        $auth = new Auth($this->samlProvider->getSettingsInfo());
        if (isset($_SESSION) && isset($_SESSION['LogoutRequestID'])) {
            $requestID = $_SESSION['LogoutRequestID'];
        } else {
            $requestID = null;
        }

        $auth->processSLO(false, $requestID);
        $errors = $auth->getErrors();
        if (empty($errors)) {
            echo '<p>Successfully logged out</p>';
            exit();
        } else {
            echo '<p>', implode(', ', $errors), '</p>';
            if ($auth->getSettings()->isDebugActive()) {
                echo '<p>' . $auth->getLastErrorReason() . '</p>';
            }
        }
    }

    public function actionMetadata(): void
    {
        try {
            #$auth = new OneLogin_Saml2_Auth($settingsInfo);
            #$settings = $auth->getSettings();
            // Now we only validate SP settings
            $settings = new Settings($this->samlProvider->getSettingsInfo(), true);
            $metadata = $settings->getSPMetadata();
            $errors = $settings->validateMetadata($metadata);
            if (empty($errors)) {
                $this->getHttpResponse()->setHeader('Content-Type', 'text/xml');
                $this->sendResponse(new Nette\Application\Responses\TextResponse($metadata));
            } else {
                throw new OneLogin_Saml2_Error(
                    'Invalid SP metadata: ' . implode(', ', $errors),
                    OneLogin_Saml2_Error::METADATA_SP_INVALID
                );
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}

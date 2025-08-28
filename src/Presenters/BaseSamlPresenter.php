<?php

declare(strict_types=1);

namespace Sitmp\Saml\Presenters;

use Nette;
use Sitmp;

abstract class BaseSamlPresenter  extends Nette\Application\UI\Presenter
{
    /** @var Sitmp\Saml\SamlProvider @inject */
    public $samlProvider;

    /** @var Nette\Application\LinkGenerator @inject */
    public $linkGenerator;

    public function __construct()
    {
        parent::__construct();
    }

    // pokud je jako parametr zadan backlink, poslu ho i do saml jako RelayState
    public function actionDefault(?string $backlink = null): void
    {
        $backlinkUrl = null;
        if ($backlink) $backlinkUrl = $this->backlink2Url($backlink);
        //$auth = new Auth($this->samlProvider->getSettingsInfo());
        //$auth->login($backlinkUrl);
        $this->samlProvider->login($backlinkUrl);
    }

    // convert backlink to url
    protected function backlink2Url(string $key): ?string
    {
        $session = $this->getSession('Nette.Application/requests');
        if (!isset($session[$key]) || ($session[$key][0] !== null && $session[$key][0] !== $this->getUser()->getId())) {
            return null;
        }
        $request = clone $session[$key][1];
        unset($session[$key]);
        return $this->linkGenerator->requestToUrl($request);
    }


    // function for handle generic error - you can overwrite it a implement logging and notification
    public function handleGenericError(\Exception $exception): void
    {
        // nejaky log ?
        $this->flashMessage("Nepodařilo se vás přihlásit (1)");
        $this->redirect($this->samlProvider->getBacklink());

    }

    // function for handle auth error - you can overwrite it a implement logging and notification
    public function handleAuthError(\Exception $exception): void
    {
        // pokud je chyba, tak co?

        // zapis do logu??
        //\Tracy\Debugger::log($exception->getMessage(), Tracy\ILogger::EXCEPTION);

        // proved redirect ???
        $this->flashMessage("Nepodařilo se vás přihlásit (2)");
        $this->redirect($this->samlProvider->getBacklink());

        /*
        // nebo vypis prazdnou sablonu, ktera hleda jestli je definovany block s chybovou instrukci pro uzivatele???
        $this->flashMessage("Nepodařilo se vás přihlásit");
        // $this->flashMessage($e->getMessage());
        $this->template->setFile(__DIR__ . '/acserror.latte') .
        // ukonci presenter a ihned vykresli sablonu
        $this->sendTemplate();
        */

        // nebo neco jineho?
    }

    public function actionAcs(): void
    {
        $acsData = $this->samlProvider->acs([$this,'handleAuthError'],[$this,'handleGenericError']);
        $saml = $this->getSession()->getSection('saml');
        foreach ($acsData as $acsKey => $acsValue) {
            $saml->set($acsKey, $acsValue);
        }
        $saml->set('backlinkUrl', $this->getHttpRequest()->getPost('RelayState'));
        // and redirect to auth / autorize backlink
        $this->redirect($this->samlProvider->getBacklink());
    }


    // pokud je Nette/Security/User a je prihlaseny, tak ho odhlas (Saml:logout a Saml:sls)
    public function logoutNetteAppUser(): void
    {
        try {
            if ($this->getUser()->isLoggedIn()) {
                $this->getUser()->logout();
            }
        } catch (Nette\InvalidStateException $e) {
            // nastane pokud v presenteru neexistuje uzivatel, tak nic
            // developer asi pro praci s uzivteli pouziva neco jineho nez Nette/Security/User
            // a v tom pripade by si mel funkci logoutNetteAppUser pretizit s vlastni logikou
        }
    }

    // what to do after successfull slo logout?? Overwrite it to implement own "after logout" functionality
    // nonsese - after succesfull slo app send redirect response to IdP
    /*public function successfulLogoutAction(): void
    {
        // echo "Successfully logged out";
        // exit();
        echo "Odhlášení proběhlo v pořádku";
        $this->terminate();
    }*/


    public function actionLogout(): void
    {
        // should I call $this->getUser->logout(); ??????
        $this->logoutNetteAppUser();
        $returnTo = null;
        $parameters = array();
        $nameId = null;
        $sessionIndex = null;
        $nameIdFormat = null;
        $samlNameIdNameQualifier = null;
        $samlNameIdSPNameQualifier = null;

        // TO-DO: co to je za logiku - tady vubec neni osetreno, co se deje, kdyz v session neni
        // najednou se nepouziva  $this->getSession(), ale rovnou $_SESSION ????
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

        //$auth = new Auth($this->samlProvider->getSettingsInfo());
        //$auth->logout($returnTo, $parameters, $nameId, $sessionIndex, false, $nameIdFormat, $samlNameIdNameQualifier, $samlNameIdSPNameQualifier);
        $this->samlProvider->logout($returnTo, $parameters, $nameId, $sessionIndex, false, $nameIdFormat, $samlNameIdNameQualifier, $samlNameIdSPNameQualifier);
    }

    public function actionSls(): void
    {
        $this->samlProvider->slo(function () {
                // should I call $this->getUser->logout(); ??????
                $this->logoutNetteAppUser();
                // only logout, let adapter to send correct LogoutResponse Success
                //$this->successfulLogoutAction();
            },[$this,'handleAuthError']);
    }

    public function actionMetadata(): void
    {
        try {
            $metadata = $this->samlProvider->getMetadata();
            $this->getHttpResponse()->setContentType('text/xml', 'UTF-8');
            $this->sendResponse(new Nette\Application\Responses\TextResponse($metadata));
        } catch (\Exception $e) {
            echo $e->getMessage();
            $this->terminate();
        }
    }
}

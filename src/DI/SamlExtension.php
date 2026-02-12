<?php
declare(strict_types=1);

namespace Sitmp\Saml\DI;

use Nette;
use Nette\DI\CompilerExtension;
use Nette\Schema\Expect;
use Sitmp\Saml\SamlProvider;


class SamlExtension extends CompilerExtension
{
    public function getConfigSchema(): Nette\Schema\Schema
    {
        return Expect::structure([
            // doplnena moznost vyberu knihovny, ktera bude funkcionalitu zajistovat
            // onelogin neumoznuje back channel SLO s POST
            'library' => Expect::anyOf('onelogin', 'litesaml','simplesaml')->default('onelogin'),
            'public_IdP_key' => Expect::string()->required()->dynamic(),
            'public_SP_key' => Expect::string()->required()->dynamic(),
            'private_SP_key' => Expect::string()->required()->dynamic(),
            'url_idp' => Expect::string()->required()->dynamic(),
            'url_idp_sign_in' => Expect::string()->required()->dynamic(),
            'url_idp_sign_out' => Expect::string()->required()->dynamic(),
            'backlink' => Expect::string()->required(),
            'saml_force_http' => Expect::bool()->default(false),
            // developer can use additional config of ""
            // more info at https://github.com/SAML-Toolkits/php-saml?tab=readme-ov-file#settings
            // examples:
            // ['debug' => true]
            // ['security' => ['wantNameId' => false]]
            // ['contactPerson' => ['technical' => ['givenName'=>'Beda','emailAddress'=>'beda@beda.cz']]]
            'extended_config' => Expect::array()->default([]),
            // developer can disable to publish singleLogoutService url to metadata
            'no_publish_slo_url' => Expect::bool()->default(false),
        ]);
    }

    /**
     * Register services
     */
    public function loadConfiguration(): void
    {
        $config = (array) $this->getConfig();
        $builder = $this->getContainerBuilder();

        $builder->addDefinition($this->prefix('provider'))
            ->setFactory(SamlProvider::class, [$config]);
    }
}

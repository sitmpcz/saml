<?php
declare(strict_types=1);

namespace Sitmp\Saml\DI;

use Nette\DI\CompilerExtension;
use Nette\Schema\Expect;
use Sitmp\Saml\SamlProvider;


class SamlExtension extends CompilerExtension
{
    public function getConfigSchema(): \Nette\Schema\Schema
    {
        return Expect::structure([
            'public_IdP_key' => Expect::string()->required()->dynamic(),
            'public_SP_key' => Expect::string()->required()->dynamic(),
            'private_SP_key' => Expect::string()->required()->dynamic(),
            'url_idp' => Expect::string()->required()->dynamic(),
            'url_idp_sign_in' => Expect::string()->required()->dynamic(),
            'url_idp_sign_out' => Expect::string()->required()->dynamic(),
            'backlink' => Expect::string()->required(),
            'saml_force_http' => Expect::bool()->default(false),
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

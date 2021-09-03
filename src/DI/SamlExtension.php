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
            'public_IdP_key' => Expect::string()->required(),
            'public_SP_key' => Expect::string()->required(),
            'private_SP_key' => Expect::string()->required(),
            'url_idp' => Expect::string()->required(),
            'url_idp_sign_in' => Expect::string()->required(),
            'url_idp_sign_out' => Expect::string()->required(),
            'backlink' => Expect::string()->required(),
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

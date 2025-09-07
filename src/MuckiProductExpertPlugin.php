<?php declare(strict_types=1);
/**
 * MuckiProductExpertPlugin
 *
 * @category   SW6 Plugin
 * @package    MuckiProductExpert
 * @copyright  Copyright (c) 2025 by Muckiware
 * @license    MIT
 * @author     Muckiware
 *
 */
namespace MuckiProductExpertPlugin;

use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;

use MuckiProductExpertPlugin\CustomFields\CustomFieldUpdater;

/**
 * Add dependencies from composer
 */
if(file_exists(dirname(__DIR__) . "/vendor/autoload.php")) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

class MuckiProductExpertPlugin extends Plugin
{
    /**
     * @throws \Exception
     */
    public function install(InstallContext $installContext): void
    {
        parent::install($installContext);
        $this->getCustomFieldUpdater()->sync();
    }

    /**
     * @throws \Exception
     */
    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);

        if ($uninstallContext->keepUserData()) {
            return;
        }

        $this->getCustomFieldUpdater()->remove();
    }

    public function activate(ActivateContext $activateContext): void
    {
        // Activate entities, such as a new payment method
        // Or create new entities here, because now your plugin is installed and active for sure
    }

    public function deactivate(DeactivateContext $deactivateContext): void
    {
        // Deactivate entities, such as a new payment method
        // Or remove previously created entities
    }

    public function update(UpdateContext $updateContext): void
    {
        parent::update($updateContext);
        $this->getCustomFieldUpdater()->sync();
    }

    public function postInstall(InstallContext $installContext): void
    {
        //postInstall
    }

    public function postUpdate(UpdateContext $updateContext): void
    {
        //postUpdate
    }
    private function getCustomFieldUpdater(): CustomFieldUpdater
    {
        /** @var EntityRepository $customFieldSetRepository */
        $customFieldSetRepository = $this->container->get('custom_field_set.repository');

        /** @var EntityRepository $customFieldRepository */
        $customFieldRepository = $this->container->get('custom_field.repository');

        return new CustomFieldUpdater(
            $customFieldSetRepository,
            $customFieldRepository,
            $this->path,
            'MuckiProductExpertPlugin'
        );
    }
}
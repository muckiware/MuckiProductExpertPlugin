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
namespace MuckiProductExpertPlugin\Services;

use Symfony\Component\HttpKernel\KernelInterface;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Core\DevOps\Environment\EnvironmentHelper;

use MuckiProductExpertPlugin\Core\Defaults as PluginDefaults;
use MuckiProductExpertPlugin\Services\Helper as PluginHelper;
use MuckiProductExpertPlugin\Core\ConfigPath;

class Settings
{
    public function __construct(
        protected SystemConfigService $config,
        protected KernelInterface $kernel,
        protected PluginHelper $pluginHelper
    )
    {}
    
    public function isEnabled(): bool
    {
        return $this->config->getBool(ConfigPath::CONFIG_PATH_ACTIVE->value);
    }
}

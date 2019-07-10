<?php

/* 
 *
 * This file is part of phar.
 *
 * phar is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * phar is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with phar.  If not, see <http://www.gnu.org/licenses/>.
 *
 * 
 * @author amine
 */

namespace Catalog\Kernel;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Catalog\DependencyInjection\CompilerPass\CollectCommandsToApplicationCompilerPass as CommandsToApplicationCompilerPass;

final class AppKernel extends Kernel
{
    protected function build(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->addCompilerPass(new CommandsToApplicationCompilerPass);
    }

    /**
     * In more complex app, add bundles here
     */
    public function registerBundles(): array
    {
        return [];
    }

    /**
     * Load all services
     */
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(APPLICATION_PATH . '/src/Resources/config/services.yml');
    }
}
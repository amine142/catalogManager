<?php

define('APPLICATION_PATH', __DIR__);
define('REAL_PATH', realpath('.'));
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
\ini_set('display_errors',1);
\error_reporting(E_ALL);
require_once __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;
use \Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;

$kernel = new Catalog\Kernel\AppKernel('dev', true);
$kernel->boot();

$container = $kernel->getContainer();
$app = $container->get(Application::class);
$app->setName('catalogManager');
$app->setVersion('1.0.0');
$helperSet = $app->getHelperSet();
AnnotationDriver::registerAnnotationClasses();
$config = new \Doctrine\ODM\MongoDB\Configuration();
$config->setProxyNamespace('Proxy');
$config->setProxyDir(APPLICATION_PATH.'/src/Resources/doctrine/Proxy');
$config->setHydratorNamespace('Hydrators');
$config->setHydratorDir(APPLICATION_PATH.'/src/Resources/doctrine/Hydrators');
$config->setMetadataDriverImpl(AnnotationDriver::create(APPLICATION_PATH . '/src/Documents'));
$config->setDefaultDB('test');
$dm = \Doctrine\ODM\MongoDB\DocumentManager::create(new \Doctrine\MongoDB\Connection("localhost:27017"), $config);

$helperSet->set(new \Doctrine\ODM\MongoDB\Tools\Console\Helper\DocumentManagerHelper($dm));
$app->addCommands(array(
    new Doctrine\ODM\MongoDB\Tools\Console\Command\ClearCache\MetadataCommand(),
    new Doctrine\ODM\MongoDB\Tools\Console\Command\Schema\CreateCommand(),
    new Doctrine\ODM\MongoDB\Tools\Console\Command\Schema\UpdateCommand(),
    new Doctrine\ODM\MongoDB\Tools\Console\Command\Schema\DropCommand(),
    new Doctrine\ODM\MongoDB\Tools\Console\Command\GenerateDocumentsCommand(),
    new Doctrine\ODM\MongoDB\Tools\Console\Command\GenerateHydratorsCommand(),
    new Doctrine\ODM\MongoDB\Tools\Console\Command\GeneratePersistentCollectionsCommand(),
    new Doctrine\ODM\MongoDB\Tools\Console\Command\GenerateProxiesCommand(),
    new Doctrine\ODM\MongoDB\Tools\Console\Command\GenerateRepositoriesCommand(),
    new Doctrine\ODM\MongoDB\Tools\Console\Command\QueryCommand()
    ));

$app->run();
__HALT_COMPILER();

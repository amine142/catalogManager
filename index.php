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

$app = new Application('catalogManager', '1.0.0');
$app->addCommands(array(new Catalog\Command\Server\ServerCommand()));

$app->run();

__HALT_COMPILER();

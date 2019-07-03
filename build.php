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
$srcRoot = realpath(__DIR__);
$buildRoot = realpath(__DIR__);


$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($srcRoot, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::LEAVES_ONLY);



//foreach ($iterator as $file) {
//
//    var_dump($file->getFilename(), $file->getPath());
//}
//die();
if (file_exists($buildRoot.'/catalog.phar')){
    unlink($buildRoot.'/catalog.phar');
}
echo "Build phar\n";

$phar = new Phar($buildRoot . '/catalog.phar', 0, 'catalog.phar');
$phar->buildFromIterator($iterator, $srcRoot);
$phar->setStub($phar->createDefaultStub("index.php" ));

exit("Build complete\n");

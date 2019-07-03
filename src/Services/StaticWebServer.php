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


namespace Catalog\Services;

use Dflydev\ApacheMimeTypes\Parser as ContentTypeParser;
use Dflydev\ApacheMimeTypes\PhpRepository;
use Psr\Log\LoggerInterface;
use React\Http\Response;

/**
 * Class StaticWebServer
 * @package   Jalle19\ReactHttpStatic
 * @copyright Copyright &copy; Sam Stenvall 2016-
 * @license   @license https://opensource.org/licenses/MIT
 */
class StaticWebServer
{


    /**
     * @var string the absolute path to the directory where files are served from
     */
    private $webroot;

   

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ContentTypeParser
     */
    private $contentTypeParser;

    /**
     * @var array
     */
    private $indexFiles = [
        'index.htm',
        'index.html',
    ];


    /**
     * StaticWebServer constructor.
     *
     * @param string               $webroot
     * @param LoggerInterface|null $logger
     */
    public function __construct( $webroot, LoggerInterface $logger = null)
    {
        if (!file_exists($webroot)) {
            throw new \InvalidArgumentException('The specified webroot path does not exist');
        }

        $this->webroot    = $webroot;
        $this->logger     = $logger;

        // Attach the request handler

        // Configure the content type parser
        $this->contentTypeParser = new ContentTypeParser();
    }


    /**
     * @return string
     */
    public function getWebroot()
    {
        return $this->webroot;
    }


    /**
     * @param string $webroot
     *
     * @return StaticWebServer
     */
    public function setWebroot($webroot)
    {
        $this->webroot = $webroot;

        return $this;
    }


    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }


    /**
     * @param LoggerInterface $logger
     *
     * @return StaticWebServer
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;

        return $this;
    }


    /**
     * @return array
     */
    public function getIndexFiles()
    {
        return $this->indexFiles;
    }


    /**
     * @param array $indexFiles
     *
     * @return StaticWebServer
     */
    public function setIndexFiles($indexFiles)
    {
        $this->indexFiles = $indexFiles;

        return $this;
    }


    


    /**
     * @param Request  $request
     * @param Response $response
     */
    public function handleRequest( $request)
    {
        $requestPath = $request->getUri()->getPath();
        $filePath    = $this->resolvePath($requestPath);
        $response = new Response();
        if ($this->logger !== null) {
            $this->logger->debug('Got HTTP request (request path: {requestPath}, resolved path: {resolvedPath})', [
                'requestPath'  => $requestPath,
                'resolvedPath' => $filePath,
            ]);
        }

        
        if (file_exists($filePath)) {
            if (is_readable($filePath)) {
                
                $response = $response->withHeader('Content-Type' ,  $this->getContentType($filePath));
                $stream = fopen($filePath, 'r');
                $body = new \RingCentral\Psr7\Stream($stream);
                $response = $response->withBody($body);
               
            } else {
                if ($this->logger !== null) {
                    $this->logger->error('HTTP request failed, file unreadable ({filePath})', [
                        'filePath' => $filePath,
                    ]);    
                }
                
                $response = new Response(403, array(
                    'Content-Type' => 'text/plain'
                        ), "Forbidden\n");
            }
        } else {
            if ($this->logger !== null) {
                $this->logger->error('HTTP request failed, file not found ({filePath})', [
                    'filePath' => $filePath,
                ]);    
            }
            
            $response = new Response(404, array(
                    'Content-Type' => 'text/plain'
                        ), "Not Found\n");
        }
        return $response;
    }


    /**
     * @param string $requestPath
     *
     * @return bool|string
     */
    public function resolvePath($requestPath)
    {
        $filePath = $this->webroot . $requestPath;

        if ($requestPath === '/') {
            foreach ($this->indexFiles as $indexFile) {
                $indexPath = $filePath . $indexFile;

                if (file_exists($indexPath)) {
                    return $indexPath;
                }
            }

            return false;
        }

        return $filePath;
    }


    /**
     * @param string $filePath
     *
     * @return string
     */
    public function getContentType($filePath)
    {
        $pathInfo = pathinfo($filePath);

        if (!isset($pathInfo['extension'])) {
            $extension = '';
        } else {
            $extension = $pathInfo['extension'];
        }

        $repository = new PhpRepository();
        $type = $repository->findType($extension);

        if ($type === null) {
            $type = 'text/plain';
        }

        return $type;
    }

}

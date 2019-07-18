<?php

namespace Catalog\Command\Server;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Factory;
use React\Http\Server as HttpServer;
use React\Socket\Server as Socket;
use Catalog\Services\StaticWebServer as HttpStaticServer;

class ServerCommand extends Command 
{

    protected function configure() 
    {
        $this
                ->setName('catalog:manager')
                ->setDescription('start catalog manager server')
                ->addArgument('commands', InputArgument::REQUIRED , 'commands to lauch') 
                ->addOption('host', null, InputOption::VALUE_OPTIONAL, 'host')
                ->addOption('port', null, InputOption::VALUE_OPTIONAL, 'port')
                ->addOption('options', null, InputOption::VALUE_OPTIONAL, 'json encoded options')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) 
    {
        if (!in_array($input->getArgument('commands'), ['start', 'stop'])){
            throw new \ErrorException('server command not found');
        }
        
        switch ($input->getArgument('commands')){
            case "start" : $this->start($input);break;
            case "stop" : $this->stop($input);break;
        }
       
    }
    
   private function start($input)
   {
        global $container;
        $host = ($input->getOption('host'))?$input->getOption('host'):'0.0.0.0';
        $port = ($input->getOption('port'))?$input->getOption('port'):8080;
        $application = $this->getApplication();
        $shutdown_password = $container->getParameter('webserver_shutdown_password');
        $loop = Factory::create();
        $path = APPLICATION_PATH.'/src/Resources/public';
        $socket = new Socket("$host:$port", $loop);
        $server = new HttpServer(function (ServerRequestInterface $request) use ($path, $socket, $shutdown_password) {
            if( null !== $request->getHeaderLine('Server-Command')){
                $hash = json_decode(base64_decode($request->getHeaderLine('Server-Command')), true);
                if ($shutdown_password === $hash['password'] && 'stop' === $hash['cmd']){
                // shutdown http server;
                    echo 'Server shutdown '. "\n";
                    sleep(3);
                    $socket->close();
                }
            };
            $staticWebServer = new HttpStaticServer($path);
            $response = $staticWebServer->handleRequest($request);
            return $response;
        });
        
        $server->listen($socket);
        echo 'Listening on ' . str_replace('tcp:', 'http:', $socket->getAddress()) . "\n";
        $loop->run();
   }
   
   private function stop($input)
   {
        $host = ($input->getOption('host')) ? $input->getOption('host') : '0.0.0.0';
        $port = ($input->getOption('port')) ? $input->getOption('port') : 8080;
        $options = ($input->getOption('options')) ? $input->getOption('options') : '';
        
        $resource = stream_socket_client('tcp://' . $host . ':'.$port);
        if (!$resource) {
            exit(1);
        }
        $loop = Factory::create();
        $stream = new \React\Stream\DuplexResourceStream($resource, $loop);
        $stream->on('end', function () {
            echo '[CLOSED]' . PHP_EOL;
        });
        $cmd = base64_encode($options);
        $stream->write("HEAD / HTTP/1.0\r\nHost: $host\r\nServer-Command: $cmd\r\n\r\n");
        $loop->run();
    }

}

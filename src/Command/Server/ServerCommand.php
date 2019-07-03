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
use React\Stream\CompositeStream as Stream;
use React\Http\Response;

class ServerCommand extends Command 
{
    protected $serverSockets ; 
    

    protected function configure() 
    {
        $this
                ->setName('catalog:manager')
                ->setDescription('start catalog manager server')
                ->addArgument('commands', InputArgument::REQUIRED , 'commands to lauch') 
                ->addOption('host', null, InputOption::VALUE_OPTIONAL, 'host')
                ->addOption('port', null, InputOption::VALUE_OPTIONAL, 'port')
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
        $host = ($input->getOption('host'))?$input->getOption('host'):'0.0.0.0';
        $port = ($input->getOption('port'))?$input->getOption('port'):8080;

        $loop = Factory::create();
        $path = APPLICATION_PATH.'/src/Resources/public'; 
        $server = new HttpServer(function (ServerRequestInterface $request) use ($path) {
            $staticWebServer = new \Catalog\Services\StaticWebServer($path);
            $response = $staticWebServer->handleRequest($request);
            return $response;
        });

        $socket = new \React\Socket\Server("$host:$port", $loop);
        $server->listen($socket);
        $this->serverSockets[] = $socket;
        echo 'Listening on ' . str_replace('tcp:', 'http:', $socket->getAddress()) . "\n";
        $loop->run();
   }
   
   private function stop($input)
   {
       var_dump($this->serverSockets);die();
        $host = ($input->getOption('host')) ? $input->getOption('host') : '0.0.0.0';
        $port = ($input->getOption('port')) ? $input->getOption('port') : 8080;
        $clientLoop = \React\EventLoop\Factory::create();

        $connector = new \React\Socket\Connector($clientLoop);
        $promise = $connector->connect("dns://$host:$port");
        $promise->then(function (Stream $stream) {
            $stream->close();
            return true;
        }, function (\ErrorException $e) {
            return false;
        });
        $clientLoop->run();
    }

}

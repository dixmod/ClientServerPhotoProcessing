<?php

namespace Dixmod\Sockets;

use Dixmod\Sockets\Exceptions;

class Client extends BaseClientServer
{
    /** @var array */
    protected $runRequiredParams = [
        'address::',
        'port::',
        'file:',
        'filter:'
    ];

    protected $message = '';
    private $filterName;
    private $fileName;

    public function __construct()
    {
        parent::__construct();

        $this->connect();
    }

    public function run()
    {
        $this->fileName = $this->runParams['file'];
        $this->filterName = $this->runParams['filter'];

        $message = base64_encode(serialize([
            'fileName' => basename($this->fileName),
            'filterName' => $this->filterName,
            'fileContent' => file_get_contents($this->fileName)
        ]))."\n";

        echo 'I send answer' . PHP_EOL;
        socket_write(
            $this->socket,
            $message,
            strlen($message)
        );
        socket_close($this->socket);
//
        echo 'I get answer' . PHP_EOL;
        $answer = '';
        while ("" !== ($buf = socket_read($this->socket, self::MESSAGE_LIMIT, PHP_BINARY_READ))) {
            $answer .= $buf;
            echo '.';
            usleep(10);
            var_dump($buf);
        }

        print_r($answer);

        socket_close($this->socket);

//        $message = unserialize(base64_decode($answer));
        //return $message['fileContent'];
//print_r($message); exit;

//        $tmpFileName = getcwd() . DIRECTORY_SEPARATOR . $message['fileName'];

//        file_put_contents(
//            $tmpFileName,
//            $message['fileContent']
//        );
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message . PHP_EOL;
    }

    private function connect()
    {
        $this->createSocket();

        $connect = socket_connect($this->socket, $this->getAddress(), $this->getPort());
        if ($connect === false) {
            throw new Exceptions\Socket('Socket connect failed');
        }
    }
}
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

    public function __construct($message = '')
    {
        parent::__construct();

        $this->fileName = $this->runParams['file'];
        $this->filterName = $this->runParams['filter'];

        $this->message = base64_encode(serialize([
            'fileName' => basename($this->fileName),
            'filterName' => $this->filterName,
            'fileContent' => file_get_contents($this->fileName)
        ]));

        $this->connect();
    }

    public function run()
    {
        socket_write(
            $this->socket,
            $this->getMessage(),
            strlen($this->message)
        );

        sleep(1);

        echo 'I get answer' . PHP_EOL;
        $answer = '';
        while (($buf = socket_read($this->socket, self::MESSAGE_LIMIT)) !== "") {
            $answer .= $buf;
            echo '.';
            usleep(10);
        }
        socket_close($this->socket);

        $message = unserialize(base64_decode($answer)) . PHP_EOL;
        //return $message['fileContent'];


        $tmpFileName = getcwd() . DIRECTORY_SEPARATOR . $message['fileName'];

        file_put_contents(
            $tmpFileName,
            $message['fileContent']
        );
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
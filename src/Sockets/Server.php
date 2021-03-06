<?php

namespace Dixmod\Sockets;

use Dixmod\File\Photo;

class Server extends BaseClientServer
{
    /** @var array */
    protected $runRequiredParams = [
        'address::',
        'port::',
        'threads::'
    ];

    /** @var int */
    protected $threads = 1;

    public function __construct()
    {
        parent::__construct();
        $this->threads = $this->runParams['threads'] ?? 1;

        $this->createServer();
    }


    public function run(): void
    {
        echo 'Server run' . PHP_EOL;
//        for ($i = 0; $i < $this->getThreads(); $i++) {
            $pid_fork = pcntl_fork();
//
//            // child process
//            if (0 === $pid_fork) {
//
                while (true) {
//                    $pid_fork = posix_getpid();
                    $socket = socket_accept($this->socket);

                    echo 'Acceptor connect: ' . $socket . PHP_EOL;

                    echo 'I receive the answer' . PHP_EOL;

                    $message = '';

                    while ("" !== ($buf = socket_read($socket, self::MESSAGE_LIMIT, PHP_BINARY_READ))) {
                        $message .= $buf;
                        echo '.';
                        usleep(10);
                    }
                    echo PHP_EOL;

                    sleep(1);
                    $message = unserialize(base64_decode($message));
                    $tmpFileName = getcwd() . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $message['fileName'];

                    file_put_contents(
                        $tmpFileName,
                        $message['fileContent']
                    );
                    echo 'I save file: ' . $tmpFileName . PHP_EOL;

                    $photo = new Photo($tmpFileName);
                    $photo->setFilter($message['filterName']);
                    $tmpResultFileName = $photo->getDir() . DIRECTORY_SEPARATOR . $message['filterName'] . '_' . $photo->getFileName();

                    $photo->save($tmpResultFileName);
                    echo 'I set filter: ' . $message['filterName'] . PHP_EOL;

                    $message['fileContent'] = file_get_contents($tmpResultFileName);
                    $message = base64_encode(serialize($message));

                    socket_write(
                        $socket,
                        $message,
                        strlen($message)
                    );

                    echo 'I send file' . PHP_EOL;

                    //unlink($tmpResultFileName);
                    //unlink($tmpFileName);

                    socket_close($socket);
                }

                socket_close($this->socket);
//                }
//            }
//        }
//
//        while (($cid = pcntl_waitpid(0, $status)) != -1) {
//            $exit_code = pcntl_wexitstatus($status);
//            echo '[' . $cid . '] exited with status: ' . $exit_code . PHP_EOL;
//        }
    }

    /**
     * @return int
     */
    private function getThreads(): int
    {
        return $this->threads;
    }

    private function createServer()
    {
        $this->createSocket();

        socket_set_option(
            $this->socket,
            SOL_SOCKET,
            SO_REUSEADDR,
            1
        );

        if (!socket_bind($this->socket, $this->getAddress(), $this->getPort())) {
            throw new Exceptions\Socket('Socket bind failed');
        }

        if (!socket_listen($this->socket, 1)) {
            throw new Exceptions\Socket('Socket listen failed');
        }
    }
}
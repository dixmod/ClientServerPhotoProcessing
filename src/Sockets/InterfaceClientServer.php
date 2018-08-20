<?php

namespace Dixmod\Sockets;

interface InterfaceClientServer
{
    const APPLICATION_PARAMS = ['address::', 'port::'];

    public function run();
}
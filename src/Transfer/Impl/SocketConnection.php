<?php

/*
 * This file is part of the Predis package.
 *
 * (c) Daniele Alessandri <suppakilla@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpCat\Transfer\Impl;

class SocketConnection
{
    private  $socket = null;

    static private $instance;
    /**
     * {@inheritdoc}
     */
    private function __construct($parameters)
    {
        $this->createResource($parameters);
    }

    /**
     * 实例化函数
     * @param $params
     * @return SocketConnection
     */
    public static function getInstance($parameters){
        if(!self::$instance instanceof self){
            self::$instance = new self($parameters);
        }
        return self::$instance;
    }

    private function __clone(){
    }


    /**
     * {@inheritdoc}
     */
    protected function createResource($parameters)
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (!is_resource($this->socket)) {
            $this->emitSocketError();
        }
        $this->setSocketOptions($this->socket, $parameters);
        $this->connectWithTimeout($this->socket, $parameters['ip'], $parameters['port']);

        return true;
    }

    /**
     * Sets options on the socket resource from the connection parameters.
     *
     * @param resource            $socket     Socket resource.
     * @param ParametersInterface $parameters Parameters used to initialize the connection.
     */
    private function setSocketOptions($socket,  $parameters)
    {
        $parameters['timeout'] = 300000;
        $timeout = array(
            'sec' => 0,
            'usec' => $parameters['timeout'],
        );

        if (!socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, $timeout)) {
            $this->emitSocketError();
        }

        if (!socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, $timeout)) {
            $this->emitSocketError();
        }

        if (!socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, true)) {
            $this->emitSocketError();
        }
    }

    /**
     * Opens the actual connection to the server with a timeout.
     *
     * @param resource            $socket     Socket resource.
     * @param string              $address    IP address (DNS-resolved from hostname)
     * @param ParametersInterface $parameters Parameters used to initialize the connection.
     *
     * @return string
     */
    private function connectWithTimeout($socket, $ip, $port)
    {
        if (socket_connect($socket, $ip, $port) === false) {
            $error = socket_last_error();
            if ($error != SOCKET_EINPROGRESS && $error != SOCKET_EALREADY) {
                $this->emitSocketError();
            }
        }
    }



    private function emitSocketError(){
        $this->disconnect();
    }

    /**
     * {@inheritdoc}
     */
    public function disconnect()
    {
        if ($this->socket) {
            socket_close($this->socket);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function write($buffer)
    {
        socket_write($this->socket, $buffer, strlen($buffer));
    }
}

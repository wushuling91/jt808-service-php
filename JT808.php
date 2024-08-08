<?php


use think\worker\Server;
use think\facade\Log;

class JT808 extends Server {
    protected $socket = 'tcp://0.0.0.0:2345';

    public function onMessage($connection, $data) {
        if ($data === '' || $data === false) {
            echo "No data received from client." . PHP_EOL;
            return;
        }

        echo "Received raw data: " . bin2hex($data) . PHP_EOL;

        if (strlen(bin2hex($data)) % 2 != 0) {
            print('length error');
            return ;
        }

        // 原始二进制数据处理
        // Log::init(['type' => 'file', 'path' => '/runtime/address/', 'apart_level' => ['info']])->write(bin2hex($data), 'info');

        // 解析数据
        try {
            $parsed = new JT808Parser();
            $parsedData = $parsed->parseMessage($data);
            print_r($parsedData);

            switch ($parsedData['header']['msgId']) {
                case '0100': // 注册应答
                    $jt808Sender = new JT808MessageSender();
                    $responseMessage = $jt808Sender->sendRegisterResponse($parsedData['header']['phoneNumber'], $parsedData['header']['msgSeq'], 0); // 结果值 0 表示成功
                    echo '发送注册消息:'.$responseMessage.PHP_EOL;
                    $connection->send($responseMessage);
                    break;
                case '0002': // 心跳消息
                    $jt808Sender = new JT808MessageSender();
                    $responseMessage = $jt808Sender->sendHeartbeatResponse($parsedData['header']['phoneNumber'], $parsedData['header']['msgSeq'], $parsedData['header']['msgSeq']);
                    $connection->send($responseMessage);
                    break;
                case '0200':    //位置信息
                    echo '位置信息: '.PHP_EOL;
                    print_r($parsedData);
                    break;


                // 处理其他消息类型
                default:
                    echo "Unknown message ID: " . $parsedData['header']['msgId'] . PHP_EOL;
                    break;
            }
        } catch (\Exception $e) {
            echo "Error parsing message: " . $e->getMessage() . PHP_EOL;
        }
    }

    public function onConnect($connection) {
        echo 'onConnect' . PHP_EOL;
    }

    public function onError($connection, $code, $msg) {
        echo "Error: {$msg} (code {$code})\n" . PHP_EOL;
        if ($connection) {
            $connection->close();
        }
    }

    public function onClose($connection) {
        echo "connect closed !" . PHP_EOL;
    }
}

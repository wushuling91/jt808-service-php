基于PHP实现物联网JT808通讯(TCP连接)
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

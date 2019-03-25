<?php
require_once __DIR__ . '/vendor/autoload.php';
require 'template_worker.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();

$log = new Logger('kassis_docs_template.recv');
$log->pushHandler(new StreamHandler('./app.log', Logger::DEBUG));
$log->addInfo('start');

$queue_name = getenv('QUEUE_NAME');

$connection = new AMQPStreamConnection('localhost', 5672, 'admin', 'admin');
$channel = $connection->channel();
$channel->queue_declare($queue_name, false, false, false, false);

echo ' [*] Waiting for messages. ('.$queue_name.') To exit press CTRL+C',"\n";
$callback = function($req) {
  $correlation_id = $req->get('correlation_id');
  $msg = $req->body;
  $data = msgpack_unpack($msg);

  $logmsg = "Received messasge: correlation_id(jobid)=".$correlation_id;
  echo " [x] ".$logmsg." \n";

  $k = new TemplateWorker();
  $result_msg = $k->exec($data);

  //$data = msgpack_pack($result_msg);
  $data = json_encode($result_msg);

  $msg = new AMQPMessage(
              $data,
              array('correlation_id' => $req->get('correlation_id'))
  );

  $req->delivery_info['channel']->basic_publish(
      $msg,
      '',
      $req->get('reply_to')
  );
  $req->delivery_info['channel']->basic_ack(
      $req->delivery_info['delivery_tag']
  );

  echo " [o] Send result: correlation_id(jobid)=".$correlation_id."\n";

};

$channel->basic_qos(null, 1, null);
$channel->basic_consume($queue_name, '', false, false, false, false, $callback);

while (count($channel->callbacks)) {
  $channel->wait();
}

$channel->close();
$connection->close();

<?php
date_default_timezone_set("PRC");
require('function.php');

$serv = new Swoole\Websocket\Server("0.0.0.0", 5050);
$serv->set(array('task_worker_num' => 4));

$serv->on('Open', function($server, $req) {
	$channel = $req->server['request_uri'];
	$channel = trim($channel, '/');
	echo "channel connection open: ".$channel.PHP_EOL;
	if ($channel) {
		$redis = getRedisConnect();
		$redis->set($req->fd, $channel);
		$redis->sAdd('s_'.$channel, $req->fd);
	}
    echo "connection open: ".$req->fd.PHP_EOL;
});

$serv->on('Message', function($server, $frame) {
    echo "Message: ".$frame->data.PHP_EOL;
    if ($frame->data == '_1_') {
    	$server->push($frame->fd, '1_1');
    } else {
	    $message = $frame->data;
	    $message = json_decode($message, true);
	    if (!$message) {
	    	$message_ret = ['username' => 'system', 'content' => '不合法的消息。'];
	    } else {
	    	$username = $message['username'];
	    	$content = $message['content'];
	    	if (!$username || !$content) {
	    		$message_ret = ['username' => 'system', 'content' => '消息不能为空。'];
	    	} else {
	    		// 检查channel和fd维护关系
	    		$message_ret = ['username' => $message['username'], 'content' => $message['content']];
	    	}
	    }
	    if ($message_ret['username'] != 'system') {
	    	// need brodecast
	    	$server->task(array('fd' => $frame->fd, 'content' => $message_ret));
	    }
	    $server->push($frame->fd, json_encode($message_ret));
	}
});

$serv->on('Task', function ($serv, $task_id, $from_id, $data) {
    echo "New AsyncTask[task_id=$task_id]".PHP_EOL;
    $fd = $data['fd'];
    $content = $data['content'];
    $message_send = json_encode($content);
    echo "broadcast[fd=$fd]".PHP_EOL;
    $redis = getRedisConnect();
    $channel = $redis->get($fd);
    if ($channel) {
    	// get fd lists
    	$all_fd = $redis->sMembers('s_'.$channel);
    	var_dump($all_fd);
    	if ($all_fd) {
    		foreach ($all_fd as $f) {
    			$f = intval($f);
    			if ($f == $fd) {
    				// 是当前fd，忽略
    				continue;
    			}
    			$ret = $serv->push($f, $message_send);
    			if ($ret) {
    				echo "broadcast send to another fd success[fd=$f]".PHP_EOL;
    			} else {
    				// if $ret is false,should remove from redis
    				echo "[Remove fd]fd is not a vaild ws websockrt, remove fd success[fd=$f]".PHP_EOL;
    				$redis->sRem('s_'.$channel, $f);
    			}
    		}
    	}
    } else {
    	echo "Fd not belong to any channel[fd=$fd]".PHP_EOL;
    }
    $serv->finish("$id -> OK");
});

$serv->on('Close', function($server, $fd) {
	$redis = getRedisConnect();
	$channel = $redis->get($fd);
	if ($channel) {
		$redis->delete($fd);
		$redis->sRem('s_'.$channel, $fd);
	}
    echo "connection close: ".$fd.PHP_EOL;
});


$serv->on('Finish', function ($serv, $task_id, $data) {
    echo "AsyncTask[$task_id] Finish: $data".PHP_EOL;
});

$serv->start();
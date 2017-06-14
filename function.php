<?php
function getRedisConnect()
{
	static $redis;
	if ($redis) {
		try {
			$redis->ping();
			return $redis;
		} catch (Exception $e) {
			// need re connect
		}
	}
	$redis = new Redis;
	$redis->connect('你的redis地址', 你的redis端口);
	$redis->auth('你的redis密码');
	return $redis;
}
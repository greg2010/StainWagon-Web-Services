<?php

require_once dirname(__FILE__) . '/../init.php';

$thread_max = 4; //max threads (4)
$users_max = 100; //max users in thread (100)

$pid = -2;
if($pid == -2){
	try{
		$connection = mysqli_connect(config::hostname, config::username, config::password);
        $selectdb = mysqli_select_db($connection, config::database);
        $query = "SELECT * FROM `users`";
		$result = mysqli_query($connection, $query);
		while($array = mysqli_fetch_assoc($result)) $userList[] = $array;
        mysqli_close($connection);

        $users_count = count($userList);
        if($users_count > $max_users){
        	$thread_count = round($users_count / $users_max);
        	if($thread_count > $thread_max){
        		$thread_count = $thread_max;
        		$users_in_thread = round($users_count / $thread_count);
        	} else $users_in_thread = $users_max;
        } else $thread_count = 1;

		$tolog = "select " . $users_count . " users, run " . $thread_count . " threads " . $users_in_thread . " users each";
	} catch (Exception $ex){
    	$tolog = "select fail: " . $ex->getMessage();
	}
}

for($t=0; $t<$thread_count; $t++){
	$pid = pcntl_fork();
	if(!$pid){
		$smta = round(microtime(1));
		$log = new logging();
		$log->put("users", $tolog);
		$users_first = $t*$users_in_thread;
		$users_last = ($t==($thread_count-1)) ? ($users_count) : (($t+1)*$users_in_thread);
		for($i=$users_first; $i<$users_last; $i++){
			$smt = round(microtime(1)*1000);
			$log->put("user", $userList[$i][login] . " (id: " . $userList[$i][id] . ")", $userList[$i][id]);
			$user = new validation($userList[$i][id], $userList[$i][accessMask]);
			$log->merge($user->verifyApiInfo(), $userList[$i][id]);
			$emt = round(microtime(1)*1000) - $smt;
			$log->put("spent", $emt . " microseconds", $userList[$i][id]);
		}
		$emta = round(microtime(1)) - $smta;
		$log->put("total spent", $emta . " seconds");
		$log->record("log.validator");
		posix_kill(posix_getpid(), SIGTERM);
	}
}

?>
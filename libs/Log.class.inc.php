<?php

// Log functions
class Log {

	// Records the given message in the log file. $quiet logs in log file but not to screen
	public static function info($message) {
		global $login_user;
		if(isset($login_user)){
			$user_str = $login_user->getUsername();
		} else {
			$user_str = "guest";
		}
        $current_time = date('Y-m-d H:i:s');
        $full_msg = $current_time . " $user_str: " . $message . "\n";
        if (__ENABLE_LOG__) {
	        $fh = fopen(self::logFile(),'a');
	        fwrite($fh,$full_msg);
			fclose($fh);
        }
    }

	// Makes sure the log file exists and return its location
    public static function logFile() {
        if (!file_exists(__LOG_FILE__)) {
            touch(__LOG_FILE__);
        }
        return __LOG_FILE__;
    }

    public static function search($search){
		$log_dir = dirname(__LOG_FILE__);
		$log_lines = array();
		$logs = scandir($log_dir);
		foreach ($logs as $log){ // Get all logs in the log directory
			if(preg_match('/^'.basename(__LOG_FILE__).'.*?$/u', $log)){
				$fh = fopen($log_dir."/".$log, 'r');
				if($fh){
					while(($line = fgets($fh)) !== false){
						if(preg_match('/^(\\d{4}-\\d{2}-\\d{2} \\d{2}:\\d{2}:\\d{2}) (.+?): (.*)$/u', $line, $matches)){ // Pull out timestamp and user
							$time = $matches[1];
							$user = $matches[2];
							$message = $matches[3];
							if(preg_match('/\\s'.$search.'(?:\\s|$)/um', $message)){ // Search for the search string in the log message
								$timestamp = strtotime($time);
								$log_lines[] = array('time'=>$timestamp, 'uid'=>$user, 'msg'=>$message);
							}
						}
					}
				}
			}
		}
		usort($log_lines, function($a, $b){ // Sort by time
			if($a['time'] == $b['time']){
				return 0;
			}
			return ($a['time'] < $b['time'])? -1 : 1;
		});

		return $log_lines;
	}
}

?>

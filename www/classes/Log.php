<?php

/**
 * ...
 */
class Log
{
	/// constants for log message levels
	const DEBUG = 10;
	const NOTICE = 20;
	const WARNING = 30;
	const ERROR = 40;
	const FATAL_ERROR = 50;

	/// string values for allowed message levels
	private static $log_levels = array(
		self::DEBUG => 'DEBUG',
		self::NOTICE => 'NOTICE',
		self::WARNING => 'WARNING',
		self::ERROR => 'ERROR',
		self::FATAL_ERROR => 'FATAL ERROR'
	);
	
	/// handle of the file where to write messages to
	private $file;
	
	/// only messages of this level and above will be actually written to the log file
	private $minLogLevel;

	/**
	 * ...
	 */
	public function __construct($filename)
	{
		$this->minLogLevel = self::NOTICE;
	
		// check if the given path exists and if not, create it
		$p = strrpos($filename, '/');
		$path = is_int($p) ? substr($filename, 0, $p) : '';
		$name = is_int($p) ? substr($filename, $p + 1) : $filename;
		if (!empty($path) && !file_exists($path))
			mkdir($path, 775, true);
	
		$this->file = fopen($filename, 'w');
		if ($this->file === false)
			throw new Exception("Cannot open log file '$filename' for write.", 500);
		
		// set immediate write to the file without buffering
		set_file_buffer($this->file, 0);
	}

	/**
	 * ...
	 */
	public function __destruct()
	{
		fclose($this->file);
	}

	/**
	 * ...
	 */
	public function getMinLogLevel()
	{
		return $this->minLogLevel;
	}
	
	/**
	 * ...
	 */
	public function setMinLogLevel($level)
	{
		$this->minLogLevel = $level;
	}
	
	/**
	 * ...
	 */
	public function write($message, $level = self::NOTICE, $http_error_code = 200)
	{
		if ($level < $this->minLogLevel) return;
		
		$timestamp = strftime('%Y-%m-%d %H:%M:%S');
		if (!isset(self::$log_levels[$level]))
			throw new Exception("Trying to log a message of an unknown level $level", 500);
		$line = "[$timestamp] " . self::$log_levels[$level] . ": $message\n";
		fwrite($this->file, $line);
		if ($level >= self::FATAL_ERROR)
			throw new Exception($message, $http_error_code);
	}
}

?>

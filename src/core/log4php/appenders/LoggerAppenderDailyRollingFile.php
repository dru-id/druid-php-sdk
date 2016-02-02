<?php

class LoggerAppenderDailyRollingFile extends LoggerAppenderFile {

	/**
	 * The 'datePattern' parameter.
	 * Determines how date will be formatted in file name.
	 * @var string
	 */
	protected $datePattern = "Ymd";
	
	/**
	 * Current date which was used when opening a file.
	 * Used to determine if a rollover is needed when the date changes.
	 * @var string
	 */
	protected $currentDate;
	
	/**
	 * The maximum size (in bytes) that the output file is allowed to reach
	 * before being rolled over to backup files.
	 *
	 * The default maximum file size is 10MB (10485760 bytes). Maximum value
	 * for this option may depend on the file system.
	 *
	 * @var integer
	 */
	protected $maxFileSize = 10485760;
	
	/**
	 * Set the maximum number of backup files to keep around.
	 *
	 * Determines how many backup files are kept before the oldest is erased.
	 * This option takes a positive integer value. If set to zero, then there
	 * will be no backup files and the log file will be truncated when it
	 * reaches <var>maxFileSize</var>.
	 *
	 * There is one backup file by default.
	 *
	 * @var integer
	 */
	protected $maxBackupIndex = 1;
	

	
	/**
	 * Appends a logging event.
	 *
	 * If the target file changes because of passage of time (e.g. at midnight)
	 * the current file is closed. A new file, with the new date, will be
	 * opened by the write() method.
	 */
	public function append(LoggerLoggingEvent $event) {
		$eventDate = $this->getDate($event->getTimestamp());
	
		// Initial setting of current date
		if (!isset($this->currentDate)) {
			$this->currentDate = $eventDate;
		}
	
		// Check if rollover is needed
		else if ($this->currentDate !== $eventDate) {
			$this->currentDate = $eventDate;
	
			
			// Close the file if it's open.
			// Note: $this->close() is not called here because it would set
			//       $this->closed to true and the appender would not recieve
			//       any more logging requests
			
			if (is_resource($this->fp)) {
				$this->write($this->layout->getFooter());
				fclose($this->fp);
			}
			$this->fp = null;
		}
		//$this->rollOver();
		//var_dump($this);
		//parent::append($event);
		$this->write($this->layout->getFooter());
	}
	
	/**
	 * Writes a string to the target file. Opens file if not already open.
	 * @param string $string Data to write.
	 */
	protected function write($string) {
		// Lazy file open
		if(!isset($this->fp)) {
			if ($this->openFile() === false) {
				return; // Do not write if file open failed.
			}
		}
	
		// Lock the file while writing and possible rolling over
		if(flock($this->fp, LOCK_EX)) {
	
			// Write to locked file
			if(fwrite($this->fp, $string) === false) {
				$this->warn("Failed writing to file. Closing appender.");
				$this->closed = true;
			}
	
			// Rollover if needed
			if (filesize($this->file) > $this->maxFileSize) {
				try {
					$this->rollOver();
				} catch (LoggerException $ex) {
					$this->warn("Rollover failed: " . $ex->getMessage() . " Closing appender.");
					$this->closed = true;
				}
			}
	
			flock($this->fp, LOCK_UN);
		} else {
			$this->warn("Failed locking file for writing. Closing appender.");
			$this->closed = true;
		}
	}
	
	private function renameArchievedLogs($fileName) {
		for($i = $this->maxBackupIndex - 1; $i >= 1; $i--) {
	
			$source = $fileName . "." . $i;
			
	
			if(file_exists($source)) {
				$target = $fileName . '.' . ($i + 1);
					
				rename($source, $target);
			}
		}
	}
	
	/**
	 * Implements the usual roll over behaviour.
	 *
	 * If MaxBackupIndex is positive, then files File.1, ..., File.MaxBackupIndex -1 are renamed to File.2, ..., File.MaxBackupIndex.
	 * Moreover, File is renamed File.1 and closed. A new File is created to receive further log output.
	 *
	 * If MaxBackupIndex is equal to zero, then the File is truncated with no backup files created.
	 *
	 * Rollover must be called while the file is locked so that it is safe for concurrent access.
	 *
	 * @throws LoggerException If any part of the rollover procedure fails.
	 */
	private function rollOver() {
		// If maxBackups <= 0, then there is no file renaming to be done.
		if($this->maxBackupIndex > 0) {
			var_dump($this->maxBackupIndex);
			// Delete the oldest file, to keep Windows happy.
			$file = $this->file . '.' . $this->maxBackupIndex;
	
			if (file_exists($file) && !unlink($file)) {
				throw new LoggerException("Unable to delete oldest backup file from [$file].");
			}
	
			// Map {(maxBackupIndex - 1), ..., 2, 1} to {maxBackupIndex, ..., 3, 2}
			$this->renameArchievedLogs($this->file);
	
		}
	
		// Truncate the active file
		//ftruncate($this->fp, 0);
		//rewind($this->fp);
	}
	
	/** Additional validation for the date pattern. */
	public function activateOptions() {
		parent::activateOptions();
	
		if (empty($this->datePattern)) {
			$this->warn("Required parameter 'datePattern' not set. Closing appender.");
			$this->closed = true;
			return;
		}
	}
	
	/** Renders the date using the configured <var>datePattern<var>. */
	protected function getDate($timestamp = null) {
		return date($this->datePattern, $timestamp);
	}
	
	/**
	 * Determines target file. Replaces %s in file path with a date.
	 */
	protected function getTargetFile() {
		return str_replace('%s', $this->currentDate, $this->file);
	}
	
	/**
	 * Sets the 'datePattern' parameter.
	 * @param string $datePattern
	 */
	public function setDatePattern($datePattern) {
		$this->setString('datePattern', $datePattern);
	}
	
	/**
	 * Returns the 'datePattern' parameter.
	 * @return string
	 */
	public function getDatePattern() {
		return $this->datePattern;
	}
	
	/**
	 * Get the maximum size that the output file is allowed to reach
	 * before being rolled over to backup files.
	 * @return integer
	 */
	public function getMaximumFileSize() {
		return $this->maxFileSize;
	}
	
	/**
	 * Set the 'maxBackupIndex' parameter.
	 * @param integer $maxBackupIndex
	 */
	public function setMaxBackupIndex($maxBackupIndex) {
		$this->setPositiveInteger('maxBackupIndex', $maxBackupIndex);
	}
	
	/**
	 * Returns the 'maxBackupIndex' parameter.
	 * @return integer
	 */
	public function getMaxBackupIndex() {
		return $this->maxBackupIndex;
	}
	
	/**
	 * Set the 'maxFileSize' parameter.
	 * @param mixed $maxFileSize
	 */
	public function setMaxFileSize($maxFileSize) {
		$this->setFileSize('maxFileSize', $maxFileSize);
	}
	
	/**
	 * Returns the 'maxFileSize' parameter.
	 * @return integer
	 */
	public function getMaxFileSize() {
		return $this->maxFileSize;
	}
	
	/**
	 * Set the 'maxFileSize' parameter (kept for backward compatibility).
	 * @param mixed $maxFileSize
	 * @deprecated Use setMaxFileSize() instead.
	 */
	public function setMaximumFileSize($maxFileSize) {
		$this->warn("The 'maximumFileSize' parameter is deprecated. Use 'maxFileSize' instead.");
		return $this->setMaxFileSize($maxFileSize);
	}
}
<?php

class Modules_FTP_Exception extends Exception {

}

class Modules_FTP {

	protected $mode;

	public function __construct($server, $user, $pass) {
		$this->connect($server);
		$this->login($user, $pass);
		$this->mode = FTP_BINARY;
	}

	public function connect($server) {
		$this->connection = ftp_connect($server);
		if(!$this->connection) {
			throw new Modules_FTP_Exception(__('Could not establish connection to server'));
		}
		return $this;
	}

	public function login($user, $pass) {
		$this->user = $user;
		$this->login = ftp_login($this->connection, $user, $pass);
		return $this;
	}

	public function get($local, $remote, $mode=NULL, $pos=0) {

		$mode = $mode == NULL || ($mode != FTP_BINARY && $mode != FTP_ASCII) ? $this->mode : $mode;
		if(!ftp_get($this->connection, $local, $remote, $mode, $pos)) {
			throw new Modules_FTP_Exception(__('Failed to get file'));
		}
		return $this;

	}

	public function put($local, $remote, $mode=NULL, $pos=0) {

		$mode = $mode == NULL || ($mode != FTP_BINARY && $mode != FTP_ASCII) ? $this->mode : $mode;
		if(!ftp_put($this->connection, $remote, $local, $mode, $pos)) {
			throw new Modules_FTP_Exception(__('Failed to upload file.'));
		}
		return $this;

	}

	public function mdir($dir) {

		if(!ftp_mkdir($this->connection, $dir)) {
			throw new Modules_FTP_Exception(__('Failed to create folder.'));
		}
		return $this;

	}

	public function rename($old, $new) {

		if(!ftp_rename($this->connection, $old, $new)) {
			throw new Modules_FTP_Exception(__('Failed to rename folder.'));
		}
		return $this;

	}

	public function rmdir($dir) {

		if(!ftp_rmdir($this->connection, $dir)) {
			throw new Modules_FTP_Exception(__('Failed to delete folder.'));
		}
		return $this;

	}

	public function delete($path) {

		if(!ftp_delete($this->connection, $path)) {
			throw new Modules_FTP_Exception(__('Failed to delete file.'));	
		}
		return $this;

	}

	public function nlist($path) {

		$content = ftp_nlist($this->connection, $path);
		return $content;

	}

	public function __destruct() {
		ftp_close($this->connection);
	}

}

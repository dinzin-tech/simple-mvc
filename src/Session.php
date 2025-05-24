<?php
	
	namespace Core;
	
	class Session
	{
		public static function start() {
			session_start();
		}
		
		public static function set($key, $value) {
			$_SESSION[$key] = $value;
		}
		
		public static function get($key, $default = null) {
			return $_SESSION[$key] ?? $default;
		}
		
		public static function getAll(): array
		{
			return $_SESSION;
		}
		
		public static function delete($key) {
			unset($_SESSION[$key]);
		}
		
		public static function destroy() {
			session_destroy();
		}
	}
<?php
	
	namespace Core;
	
	class LocalMailer
	{
		private $mailsDirectory;
		
		public function __construct($directory = 'mails') {
			// Set the directory for storing emails
			$this->mailsDirectory = BASE_PATH . '/' .$directory;
			
			// Create the directory if it doesn't exist
			if (!is_dir($this->mailsDirectory)) {
				mkdir($this->mailsDirectory, 0755, true);
			}
			
			//TODO: fetch SMPT config data etc.
		}
		
		public function storeEmail($to, $subject, $message, $headers): bool
		{
			// Generate a unique file name
			$fileName = $this->mailsDirectory . $subject . '_' . date('Y-m-d_H-i-s') . '_' . uniqid() . '.html';
			
			// Prepare the email content
			$emailContent = "<strong>To:</strong> {$to}<br>";
			$emailContent .= "<strong>Subject:</strong> {$subject}<br><br>";
			$emailContent .= "<strong>Headers:</strong><br>" . nl2br($headers) . "<br><br>";
			$emailContent .= "<strong>Message:</strong><br>{$message}";
			
			// Write the content to the file
			return file_put_contents($fileName, $emailContent) !== false;
		}
		
		//TODO: implement full functionality of sending emails
		public function sendEmail($to, $subject, $message, $headers): bool
		{
			$this->storeEmail($to, $subject, $message, $headers);
			
			//TODO: implement mail() function here
			mail($to, $subject, $message, $headers);
			
			return true;
		}
	}
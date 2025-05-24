<?php
	
	namespace Core;
	
	use Random\RandomException;
    use Core\Session;
    use Core\Http\Request;
	
	class Form
	{
		private array  $fields            = [];
		private string $csrfToken;
		private string $method            = 'POST';
		private string $action            = '';
		private string $submitButtonValue = 'Submit';
		private array  $errors            = [];
		
		/**
		 * @throws RandomException
		 */
		public function __construct()
		{
			if(Session::get('csrf_token'))
			{
				$this->csrfToken = Session::get('csrf_token');
			}
			else {
				$this->csrfToken = bin2hex(random_bytes(32));
				Session::set('csrf_token', $this->csrfToken);
			}
		}
		
		public function setMethod($method): void
		{
			$this->method = $method;
		}
		
		public function setAction($action): void
		{
			$this->action = $action;
		}
		
		public function setSubmitButtonName($value): void
		{
			$this->submitButtonValue = $value;
		}
		
		public function addField($type, $name, $label = null, $value = null, $attributes = []): void
		{
			$this->fields[] = [
				'type'       => $type,
				'name'       => $name,
				'label'      => $label,
				'value'      => $value,
				'attributes' => $attributes,
			];
		}

		public function add($type, $name, $label = null, $value = null, $attributes = [])
		{
			$this->addField($type, $name, $label, $value, $attributes);
		}
		
		/*public function render(): string
		{
			$html = '<form method="' . $this->method . '" action="' . $this->action . '">';
			$html .= '<input type="hidden" name="csrf_token" value="' . $this->csrfToken . '">';
			foreach ($this->fields as $field) {
				$html .= '<label for="' . $field['name'] . '">' . $field['label'] . '</label>';
				if($field['type'] == 'textarea') {
					$html .= '<textarea name="' . $field['name'] . '" value="' . $field['value'] . '"';
					foreach ($field['attributes'] as $attr => $val) {
						$html .= ' ' . $attr . '="' . $val . '"';
					}
					$html .= '></textarea>';
				}
				else {
					$html .= '<input type="' . $field['type'] . '" name="' . $field['name'] . '" value="' . $field['value'] . '"';
					foreach ($field['attributes'] as $attr => $val) {
						$html .= ' ' . $attr . '="' . $val . '"';
					}
					$html .= '>';
				}
			}
			$html .= '<button type="submit">' . $this->submitButtonValue . '</button>';
			$html .= '</form>';
			return $html;
		}*/
		
		public function render(): string
		{
			$html = '<form method="' . htmlspecialchars($this->method) . '" action="' . htmlspecialchars($this->action) . '"';
			// Add `enctype` attribute if a file input is present
			if (array_search('file', array_column($this->fields, 'type')) !== false) {
				$html .= ' enctype="multipart/form-data"';
			}
			$html .= '>';
			
			// Add CSRF token field
			$html .= '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($this->csrfToken) . '">';
			
			// Generate form fields
			foreach ($this->fields as $field) {
				$html .= '<label for="' . htmlspecialchars($field['name']) . '">' . htmlspecialchars($field['label']) . '</label>';
				$attributes = '';
				
				// Add all custom attributes
				foreach ($field['attributes'] as $attr => $val) {
					$attributes .= ' ' . htmlspecialchars($attr) . '="' . htmlspecialchars($val) . '"';
				}
				
				// Handle specific input types
				if ($field['type'] === 'textarea') {
					$html .= '<textarea name="' . htmlspecialchars($field['name']) . '" id="' . htmlspecialchars($field['name']) . '"' . $attributes . '>';
					$html .= htmlspecialchars($field['value']);
					$html .= '</textarea>';
				} elseif (in_array($field['type'], ['checkbox', 'radio'], true)) {
					$html .= '<input type="' . htmlspecialchars($field['type']) . '" name="' . htmlspecialchars($field['name']) . '" id="' . htmlspecialchars($field['name']) . '" value="' . htmlspecialchars($field['value']) . '"';
					if (!empty($field['checked'])) {
						$html .= ' checked';
					}
					$html .= $attributes . '>';
				} elseif ($field['type'] === 'file') {
					$html .= '<input type="file" name="' . htmlspecialchars($field['name']) . '" id="' . htmlspecialchars($field['name']) . '"' . $attributes . '><br/><br/>';
				} else {
					$html .= '<input type="' . htmlspecialchars($field['type']) . '" name="' . htmlspecialchars($field['name']) . '" id="' . htmlspecialchars($field['name']) . '" value="' . htmlspecialchars($field['value']) . '"' . $attributes . '>';
				}
			}
			
			// Add submit button
			$html .= '<button type="submit">' . htmlspecialchars($this->submitButtonValue) . '</button>';
			$html .= '</form>';
			
			return $html;
		}
		
		
		/*public function validateCSRFToken($token): bool
		{
			if ($token !== $this->csrfToken) {
				$this->addError('CSRF token invalid');
				return false;
			}
			
			return true;
		}*/

		public function validateCSRFToken($token): bool
		{
			$sessionToken = Session::get('csrf_token');
			if (!$sessionToken || $token !== $sessionToken) {
				$this->addError('Invalid CSRF token.');
				return false;
			}
			return true;
		}
		
		/*public function handle(Request $request): void
		{
			if ($request->getMethod() === 'POST') {
				foreach ($this->fields as $index => $field) {
					$this->fields[$index]['value'] = $request->get($field['name']);
				}
			}
		}*/
		
		public function handle(Request $request): void
		{
			if ($request->getMethod() === 'POST') {
				
				foreach ($this->fields as $index => $field) {
					if ($field['type'] === 'file') {
						$file = $_FILES[$field['name']];
						if($file['error'] === 4) {
							continue;
						}
						// File validation (adjust as needed)
						$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
						$maxSize = 1 * 1024 * 1024; // 2MB
						
						if (!in_array(pathinfo($file['name'], PATHINFO_EXTENSION), $allowedExtensions)) {
							
							$this->addError('Invalid file type. Only ' . implode(', ', $allowedExtensions) . ' files are allowed.');
							continue;
						}
						
						if ($file['size'] > $maxSize) {
							
							$this->addError('File size exceeds the maximum limit of 2MB.');
							continue;
						}
						
						// Upload the file
						$uploadDir  = 'uploads/';
						$uploadPath = __DIR__ . '/../public/' . $uploadDir; // Replace with your desired upload directory
						$fileName   = bin2hex(random_bytes(4)) . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
						$targetFile = $uploadPath . $fileName;
						
						if (move_uploaded_file($file['tmp_name'], $targetFile)) {
							
							$this->fields[$index]['value'] = $uploadDir.$fileName;
						} else {
							
							$this->addError('Error uploading file.');
						}
					} else {
						
						$this->fields[$index]['value'] = $request->get($field['name']);
					}
				}
			}
		}
		
		public function clearForm(Request $request)
		{
			if ($request->getMethod() === 'POST') {
				foreach ($this->fields as $index => $field) {
					$this->fields[$index]['value'] = null;
				}
			}
		}
		
		public function validate(Request $request): bool
		{
			foreach ($this->fields as $field) {
				$value = $field['value'];
				$rules = $field['attributes'] ?? [];
				
				if (isset($rules['required']) && $rules['required'] && empty($value)) {
					$this->addError("{$field['name']} is required.");
				}
			}
			
			$this->validateCSRFToken($request->get('csrf_token'));
			
			return empty($this->errors);
		}
		
		public function getErrors(): array
		{
			return $this->errors;
		}
		
		public function isSubmitted(): bool
		{
			return $_SERVER['REQUEST_METHOD'] === 'POST';
		}
		
		public function addError($error): void
		{
			$this->errors[] = ['error' => $error];
		}
		
		public function getFormData(): array
		{
			$formData = $_POST;
			
			foreach ($formData as $key => $value) {
				
				$formData[$key] = filter_var($value, FILTER_SANITIZE_STRING);
			}
			
			foreach ($this->fields as $index => $field) {
				
				if($field['type'] === 'file') {
					if(!isset($formData[$field['name']])) {
						$formData[$field['name']] = $field['value'];
					}
				}
			}
			
			return $formData;
		}
	}
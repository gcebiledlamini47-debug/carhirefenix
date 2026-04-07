<?php
/**
 * Validator.php - Input validation and sanitization
 */

class Validator {
    private $errors = [];
    
    /**
     * Validate required field
     */
    public function required($field, $value) {
        if (empty(trim($value))) {
            $this->errors[$field] = ucfirst($field) . ' is required';
            return false;
        }
        return true;
    }
    
    /**
     * Validate email
     */
    public function email($field, $value) {
        if (!SecurityHelper::isValidEmail($value)) {
            $this->errors[$field] = 'Invalid email address';
            return false;
        }
        return true;
    }
    
    /**
     * Validate phone
     */
    public function phone($field, $value) {
        if (!SecurityHelper::isValidPhone($value)) {
            $this->errors[$field] = 'Invalid phone number';
            return false;
        }
        return true;
    }
    
    /**
     * Validate minimum length
     */
    public function minLength($field, $value, $min) {
        if (strlen($value) < $min) {
            $this->errors[$field] = ucfirst($field) . " must be at least $min characters";
            return false;
        }
        return true;
    }
    
    /**
     * Validate maximum length
     */
    public function maxLength($field, $value, $max) {
        if (strlen($value) > $max) {
            $this->errors[$field] = ucfirst($field) . " must not exceed $max characters";
            return false;
        }
        return true;
    }
    
    /**
     * Validate numeric value
     */
    public function numeric($field, $value) {
        if (!is_numeric($value)) {
            $this->errors[$field] = ucfirst($field) . ' must be a number';
            return false;
        }
        return true;
    }
    
    /**
     * Validate date format
     */
    public function date($field, $value, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $value);
        if (!$d || $d->format($format) !== $value) {
            $this->errors[$field] = ucfirst($field) . " must be in $format format";
            return false;
        }
        return true;
    }
    
    /**
     * Validate future date
     */
    public function futureDate($field, $value, $format = 'Y-m-d') {
        if (!$this->date($field, $value, $format)) {
            return false;
        }
        
        $date = DateTime::createFromFormat($format, $value);
        if ($date <= new DateTime()) {
            $this->errors[$field] = ucfirst($field) . ' must be in the future';
            return false;
        }
        return true;
    }
    
    /**
     * Validate past date
     */
    public function pastDate($field, $value, $format = 'Y-m-d') {
        if (!$this->date($field, $value, $format)) {
            return false;
        }
        
        $date = DateTime::createFromFormat($format, $value);
        if ($date >= new DateTime()) {
            $this->errors[$field] = ucfirst($field) . ' must be in the past';
            return false;
        }
        return true;
    }
    
    /**
     * Validate integer
     */
    public function integer($field, $value) {
        if (!filter_var($value, FILTER_VALIDATE_INT)) {
            $this->errors[$field] = ucfirst($field) . ' must be an integer';
            return false;
        }
        return true;
    }
    
    /**
     * Validate field is one of allowed values
     */
    public function inArray($field, $value, $allowed = []) {
        if (!in_array($value, $allowed)) {
            $this->errors[$field] = ucfirst($field) . ' has invalid value';
            return false;
        }
        return true;
    }
    
    /**
     * Get validation errors
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Check if validation passed
     */
    public function passes() {
        return empty($this->errors);
    }
    
    /**
     * Sanitize input
     */
    public static function sanitize($value) {
        return trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
    }
    
    /**
     * Sanitize array
     */
    public static function sanitizeArray($data) {
        $sanitized = [];
        foreach ($data as $key => $value) {
            $sanitized[$key] = is_array($value) ? self::sanitizeArray($value) : self::sanitize($value);
        }
        return $sanitized;
    }
}
?>

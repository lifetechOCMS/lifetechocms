<?php

class LtValidator
{
    protected static array $errors = [];

    public static function validate(array $data, array $rules)
    {
        self::$errors = [];

        foreach ($rules as $field => $ruleSet) {
            $value = $data[$field] ?? null;
            $rules = explode('|', $ruleSet);

            foreach ($rules as $rule) {
                if ($rule === 'required' && empty($value)) {
                    self::addError($field, "The $field field is required.");
                }

                if (str_starts_with($rule, 'min:')) {
                    $min = (int) explode(':', $rule)[1];
                    if (strlen($value) < $min) {
                        self::addError($field, "The $field must be at least $min characters.");
                    }
                }

                if (str_starts_with($rule, 'max:')) {
                    $max = (int) explode(':', $rule)[1];
                    if (strlen($value) > $max) {
                        self::addError($field, "The $field must not exceed $max characters.");
                    }
                }

                if ($rule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    self::addError($field, "The $field must be a valid email.");
                }

                if ($rule === 'numeric' && !is_numeric($value)) {
                    self::addError($field, "The $field must be numeric.");
                }
            }
        }

        //return empty(self::$errors);
          if (!empty(self::$errors)) { 
              echo LtResponse::json(self::$errors);
            }
        return true;
    } 
    public static function errors(): array
    {
        return self::$errors;
    }

    public static function passed(): bool
    {
        return empty(self::$errors);
    }

    protected static function addError(string $field, string $message): void
    {
        self::$errors[$field] = $message;
        //self::$errors[$field][] = $message;
    }
}

?>
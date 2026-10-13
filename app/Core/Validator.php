<?php

namespace App\Core;

class Validator {
    private array $data;
    private array $rules;
    private array $errors = [];

    public function __construct(array $data, array $rules) {
        $this->data = $data;
        $this->rules = $rules;
    }

    public static function make(array $data, array $rules): self {
        $validator = new self($data, $rules);
        $validator->validate();
        return $validator;
    }

    public function validate(): bool {
        $this->errors = [];

        foreach ($this->rules as $field => $fieldRules) {
            $value = $this->data[$field] ?? null;
            $ruleList = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;

            foreach ($ruleList as $rule) {
                $ruleName = $rule;
                $ruleParam = null;

                if (str_contains($rule, ':')) {
                    [$ruleName, $ruleParam] = explode(':', $rule, 2);
                }

                $this->applyRule($field, $value, $ruleName, $ruleParam);
            }
        }

        return empty($this->errors);
    }

    private function applyRule(string $field, mixed $value, string $rule, ?string $param): void {
        $label = ucfirst(str_replace('_', ' ', $field));

        switch ($rule) {
            case 'required':
                if ($value === null || trim((string)$value) === '') {
                    $this->addError($field, "$label is required.");
                }
                break;

            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "$label must be a valid email address.");
                }
                break;

            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->addError($field, "$label must be a number.");
                }
                break;

            case 'integer':
                if (!empty($value) && filter_var($value, FILTER_VALIDATE_INT) === false) {
                    $this->addError($field, "$label must be an integer.");
                }
                break;

            case 'min':
                if (is_numeric($value) && (float)$value < (float)$param) {
                    $this->addError($field, "$label must be at least $param.");
                } elseif (is_string($value) && mb_strlen($value) < (int)$param) {
                    $this->addError($field, "$label must be at least $param characters.");
                }
                break;

            case 'max':
                if (is_numeric($value) && (float)$value > (float)$param) {
                    $this->addError($field, "$label cannot exceed $param.");
                } elseif (is_string($value) && mb_strlen($value) > (int)$param) {
                    $this->addError($field, "$label cannot exceed $param characters.");
                }
                break;

            case 'in':
                $allowed = explode(',', (string)$param);
                if (!empty($value) && !in_array((string)$value, $allowed, true)) {
                    $this->addError($field, "$label must be one of: " . implode(', ', $allowed));
                }
                break;

            case 'matches':
                $targetValue = $this->data[$param] ?? null;
                if ($value !== $targetValue) {
                    $targetLabel = ucfirst(str_replace('_', ' ', (string)$param));
                    $this->addError($field, "$label does not match $targetLabel.");
                }
                break;

            case 'date':
                if (!empty($value)) {
                    $d = \DateTime::createFromFormat('Y-m-d', $value);
                    if (!$d || $d->format('Y-m-d') !== $value) {
                        $this->addError($field, "$label must be a valid date (YYYY-MM-DD).");
                    }
                }
                break;

            case 'phone':
                if (!empty($value) && !preg_match('/^[0-9+\-\s()]{7,20}$/', (string)$value)) {
                    $this->addError($field, "$label must be a valid phone number.");
                }
                break;

            case 'unique':
                // unique:table,column[,ignore_id]
                if (!empty($value) && $param) {
                    $parts = explode(',', $param);
                    $table = $parts[0];
                    $column = $parts[1] ?? $field;
                    $ignoreId = $parts[2] ?? null;

                    $sql = "SELECT COUNT(*) FROM `$table` WHERE `$column` = :val";
                    $params = [':val' => $value];
                    if ($ignoreId) {
                        $sql .= " AND `id` != :ignore_id";
                        $params[':ignore_id'] = $ignoreId;
                    }

                    $stmt = Database::getConnection()->prepare($sql);
                    $stmt->execute($params);
                    if ($stmt->fetchColumn() > 0) {
                        $this->addError($field, "This $label is already in use.");
                    }
                }
                break;
        }
    }

    public function addError(string $field, string $message): void {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    public function passes(): bool {
        return empty($this->errors);
    }

    public function fails(): bool {
        return !empty($this->errors);
    }

    public function errors(): array {
        return $this->errors;
    }

    public function first(string $field): ?string {
        return $this->errors[$field][0] ?? null;
    }

    public function firstError(): ?string {
        foreach ($this->errors as $fieldErrors) {
            if (!empty($fieldErrors)) {
                return $fieldErrors[0];
            }
        }
        return null;
    }
}

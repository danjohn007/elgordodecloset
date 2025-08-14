<?php

namespace App\Helpers;

class Validation
{
    private $errors = [];

    public function validate($data, $rules)
    {
        $this->errors = [];

        foreach ($rules as $field => $rule) {
            $ruleList = is_string($rule) ? explode('|', $rule) : $rule;
            $value = $data[$field] ?? null;

            foreach ($ruleList as $singleRule) {
                $this->applyRule($field, $value, $singleRule, $data);
            }
        }

        return empty($this->errors);
    }

    private function applyRule($field, $value, $rule, $data)
    {
        $params = [];
        if (strpos($rule, ':') !== false) {
            [$rule, $paramString] = explode(':', $rule, 2);
            $params = explode(',', $paramString);
        }

        switch ($rule) {
            case 'required':
                if (empty($value)) {
                    $this->addError($field, "El campo {$field} es obligatorio");
                }
                break;

            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "El campo {$field} debe ser un email válido");
                }
                break;

            case 'min':
                $min = (int) $params[0];
                if (!empty($value) && strlen($value) < $min) {
                    $this->addError($field, "El campo {$field} debe tener al menos {$min} caracteres");
                }
                break;

            case 'max':
                $max = (int) $params[0];
                if (!empty($value) && strlen($value) > $max) {
                    $this->addError($field, "El campo {$field} no debe exceder {$max} caracteres");
                }
                break;

            case 'confirmed':
                $confirmField = $field . '_confirmation';
                if ($value !== ($data[$confirmField] ?? null)) {
                    $this->addError($field, "La confirmación del campo {$field} no coincide");
                }
                break;

            case 'unique':
                if (!empty($value)) {
                    $table = $params[0];
                    $column = $params[1] ?? $field;
                    $excludeId = $params[2] ?? null;
                    
                    if ($this->isUnique($table, $column, $value, $excludeId)) {
                        $this->addError($field, "El valor del campo {$field} ya existe");
                    }
                }
                break;

            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->addError($field, "El campo {$field} debe ser numérico");
                }
                break;

            case 'integer':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
                    $this->addError($field, "El campo {$field} debe ser un número entero");
                }
                break;

            case 'date':
                if (!empty($value) && !$this->isValidDate($value)) {
                    $this->addError($field, "El campo {$field} debe ser una fecha válida");
                }
                break;

            case 'time':
                if (!empty($value) && !$this->isValidTime($value)) {
                    $this->addError($field, "El campo {$field} debe ser una hora válida");
                }
                break;

            case 'phone':
                if (!empty($value) && !$this->isValidPhone($value)) {
                    $this->addError($field, "El campo {$field} debe ser un teléfono válido");
                }
                break;

            case 'in':
                if (!empty($value) && !in_array($value, $params)) {
                    $this->addError($field, "El campo {$field} debe ser uno de: " . implode(', ', $params));
                }
                break;

            case 'between':
                $min = (int) $params[0];
                $max = (int) $params[1];
                if (!empty($value) && (is_numeric($value) && ($value < $min || $value > $max))) {
                    $this->addError($field, "El campo {$field} debe estar entre {$min} y {$max}");
                }
                break;
        }
    }

    private function addError($field, $message)
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    private function isUnique($table, $column, $value, $excludeId = null)
    {
        $db = \App\Core\DB::getInstance();
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = ?";
        $params = [$value];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = $db->fetch($sql, $params);
        return $result['count'] > 0;
    }

    private function isValidDate($date)
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    private function isValidTime($time)
    {
        $t = \DateTime::createFromFormat('H:i', $time);
        return $t && $t->format('H:i') === $time;
    }

    private function isValidPhone($phone)
    {
        // Basic phone validation - adjust pattern as needed
        return preg_match('/^[\+]?[0-9\-\s\(\)]{10,20}$/', $phone);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getFirstError($field)
    {
        return $this->errors[$field][0] ?? null;
    }

    public function hasErrors()
    {
        return !empty($this->errors);
    }

    public function hasError($field)
    {
        return isset($this->errors[$field]);
    }
}
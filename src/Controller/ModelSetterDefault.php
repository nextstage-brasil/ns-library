<?php

namespace NsLibrary\Controller;

use Exception;
use NsUtil\Helper;

/**
 * Description of EntityManager
 *
 * @author NextStage
 */
class ModelSetterDefault {
    private static $config = [
        'string' => 'string',
        'text' => 'string',
        'json' => 'json',
        'jsonb' => 'json',
        'bool' => 'bool',
        'boolean' => 'bool',
        'timestamp' => 'datetime',
        'datetime' => 'datetime',
        'date' => 'date',
        'double' => 'double',
        'decimal' => 'double',
        'int' => 'int'
    ];

    public static function getTemplate($type) {
        $fn = 'set' . ucwords((string) self::$config[$type] ?? '');
        if (!method_exists(ModelSetterDefault::class, $fn)) {
            throw new Exception("Entities Create: Invalid Template Type: " . $type);
        }
        return  '
        /**
         * Setter to %nome%
         *
         * @param ' . $type . ' $content
         * @return self
         */    
        public function set%nomeFunction%($content) : self {
            ModelSetterDefault::' . $fn . '(
                $content,
                $this->%nome%,
                "%nome%",
                "%coments%",
                (int) %maxsize%,
                $this->error,
                "%tipo%", 
                (bool) %notnull%
            );
            return $this;
        }
    
        /**
         * Getter to %nome%
         *
         * @return ' . $type . '
         */
        public function get%nomeFunction%() {
            return $this->%nome%;
        }
        ';
    }

    public static function getTemplateObject() {
        return '
            public function set%nomeFunction%($%nome%) {
                $this->%nome% = (($%nome% instanceof %nome%)? $%nome% : new %nome%($%nome%));
                return $this;
            }
        
            public function get%nomeFunction%() {
                return $this->%nome%;
            }
        ';
    }

    public static function getTemplateExterna() {
        return '
            public function set%nomeFunction%($%nome%) {
                $this->%nome% = (object) $%nome%;
                return $this;
            }
        
            public function get%nomeFunction%() {
                return $this->%nome%;
            }
        ';
    }

    public static function setString(
        $content,
        &$varToSet,
        string $fieldName,
        string $comentError,
        int $maxsize,
        array &$error,
        string $type,
        bool $notNull = false
    ): void {

        $content =  Helper::getValByType(
            is_array($content) ? $content[$fieldName] : $content,
            'string'
        );

        if ($notNull && strlen((string)$content) <= 0) {
            $error[$fieldName] = $comentError;
        } else {
            unset($error[$fieldName]);
            $varToSet = (string) mb_substr((string)$content, 0, $maxsize);
        }
    }

    public static function setJson(
        $content,
        &$varToSet,
        string $fieldName,
        string $comentError,
        int $maxsize,
        array &$error,
        string $type,
        bool $notNull = false
    ): void {
        if (!is_array($content) && !is_object($content)) {
            $content = json_decode((string) $content, true);
        }
        $content = json_encode(
            str_replace('&#34;', '\u0022', $content),
            JSON_HEX_QUOT | JSON_HEX_APOS
        );

        if ($notNull && (null === $content  || json_last_error() > 0)) {
            $error[$fieldName] = $comentError;
        } else {
            unset($error[$fieldName]);
            $varToSet = json_last_error() > 0 ? json_encode([]) : $content;
        }
    }

    public static function setBool(
        $content,
        &$varToSet,
        string $fieldName,
        string $comentError,
        int $maxsize,
        array &$error,
        string $type,
        bool $notNull = false
    ): void {
        if (is_array($content)) {
            $content = $content[$fieldName];
        }
        if (gettype($content) === 'boolean') {
            $varToSet = (string) $content ? 'true' : 'false';
        } else {
            $varToSet = (string) ((Helper::compareString('true', (string) $content)) ? 'true' : 'false');
        }
    }

    public static function setDatetime(
        $content,
        &$varToSet,
        string $fieldName,
        string $comentError,
        int $maxsize,
        array &$error,
        string $type,
        bool $notNull = false
    ): void {

        $content = Helper::getValByType($content, 'string');

        if ($notNull && strlen((string)$content) <= 8) {
            $error[$fieldName] = $comentError;
        } else {
            unset($error[$fieldName]);
            $date = Helper::formatDate($content, 'c', true);
            if ($notNull && !$date) {
                $error[$fieldName] = $comentError . ' - Invalid date';
            } else {
                $varToSet = (($date) ? (string) $date : null);
            }
        }
    }

    public static function setDate(
        $content,
        &$varToSet,
        string $fieldName,
        string $comentError,
        int $maxsize,
        array &$error,
        string $type,
        bool $notNull = false
    ): void {

        $content = Helper::getValByType($content, 'string');

        if ($notNull && strlen((string)$content) < 8) {
            $error[$fieldName] = $comentError;
        } else {
            unset($error[$fieldName]);
            $date = Helper::formatDate($content);
            if ($notNull && !$date) {
                $error[$fieldName] = $comentError . ' - Invalid date';
            } else {
                $varToSet = (($date) ? (string) $date : null);
            }
        }
    }

    public static function setDouble(
        $content,
        &$varToSet,
        string $fieldName,
        string $comentError,
        int $maxsize,
        array &$error,
        string $type,
        bool $notNull = false
    ): void {

        $content =  Helper::getValByType(
            is_array($content) ? $content[$fieldName] : $content,
            'double'
        );

        if ($notNull && strlen((string)$content) <= 0) {
            $error[$fieldName] = $comentError;
        } else {
            unset($error[$fieldName]);
            $content = (float) Helper::decimalFormat($content);
            if ($notNull && $content == 0) {
                $error[$fieldName] = $comentError;
            } else {
                $varToSet =  Helper::getValByType($content, 'double');
            }
        }
    }

    public static function setInt(
        $content,
        &$varToSet,
        string $fieldName,
        string $comentError,
        int $maxsize,
        array &$error,
        string $type,
        bool $notNull = false
    ): void {

        $content =  Helper::getValByType(
            is_array($content) ? $content[$fieldName] : $content,
            'int'
        );

        if ($notNull && strlen((string)$content) <= 0) {
            $error[$fieldName] = $comentError;
        } else {
            unset($error[$fieldName]);
            $content = (int) Helper::parseInt($content);
            if ($notNull && $content == 0) {
                $error[$fieldName] = $comentError;
            } else {
                $varToSet =  Helper::getValByType($content, 'int');
            }
        }
    }

    public static function setDefault(
        $content,
        &$varToSet,
        string $fieldName,
        string $comentError,
        int $maxsize,
        array &$error,
        string $type,
        bool $notNull = false
    ): void {

        $content =  Helper::getValByType(
            is_array($content) ? $content[$fieldName] : $content,
            $type
        );

        if ($notNull && strlen((string)$content) <= 0) {
            $error[$fieldName] = $comentError;
        } else {
            unset($error[$fieldName]);
            $varToSet = $content;
        }
    }
}

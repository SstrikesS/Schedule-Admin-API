<?php

namespace App\Libs;

use App\Consts\DbTypes;

class NorIntoDB
{
    protected ?array $change = null;
    protected ?array $data   = null;

    /**
     * Sử dụng cho trường hợp cập nhật chỉnh sửa dữ liệu vào database
     * - So sánh dữ liệu post với dữ liệu đang được lưu trong DB
     * - Nếu dữ liệu giống với DB thi clear vì không có sự thay đổi
     */
    public function viaDB(array $input, array|object $info): bool
    {
        if (is_object($info)) {
            $info = (array)$info;
        }

        $data = $change = [];

        foreach ($input as $key => $value) {
            if (!in_array($key, array_keys($info))) {
                continue;
            }

            if (is_array($value)) {
                $value = formatJsonToSQL($value);
            } elseif (is_string($value)) {
                $value = trim($value);
            }

            if (gettype($value) != gettype($info[$key])) {
                switch (gettype($info[$key])) {
                    case 'string':
                        $value = strval($value);

                        if ($value == '') {
                            $value = null;
                        }

                        break;
                    case 'integer':
                        $value = intval($value);

                        break;
                    case 'double':
                        $value = floatval($value);

                        break;
                    case 'boolean':
                        if (is_string($value)) {
                            if ($value === 'true') {
                                $value = true;
                            } elseif ($value === '1') {
                                $value = true;
                            } elseif ($value === 'false') {
                                $value = false;
                            } elseif ($value === '0') {
                                $value = false;
                            } else {
                                $value = boolval($value);
                            }
                        } elseif (is_int($value)) {
                            if ($value === 1) {
                                $value = true;
                            } elseif ($value === 0) {
                                $value = false;
                            } else {
                                $value = boolval($value);
                            }
                        } elseif (is_array($value)) {
                            $value = count($value) > 0;
                        } else {
                            $value = boolval($value);
                        }

                        break;
                }
            }

            if ($value != $info[$key]) {
                $data[$key] = $value;

                $change[$key] = [$info[$key], $value];
            }
        }

        if ($data && $change) {
            $this->data = $data;
            $this->change = $change;

            return true;
        }

        return false;
    }

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function setChange(array $data): static
    {
        $this->change = $data;

        return $this;
    }

    public function getData(?array $merge = null): ?array
    {
        if ($merge && $this->data) {
            return [
                ...$this->data,
                ...$merge
            ];
        }

        return $this->data;
    }

    public function getChange(?array $merge = null): ?array
    {
        if ($merge && $this->change) {
            return [
                ...$this->change,
                ...$merge
            ];
        }

        return $this->change;
    }

    /**
     * Sử dụng cho trường hợp insert dữ liệu vào database
     * - So sánh dữ liệu post với các trường của DB được định nghĩa sẵn
     * - Nếu so với được định nghĩa không có thì clear
     */
    public function viaArray(array $input, array $compare): ?array
    {
        $data = [];

        foreach ($input as $key => $value) {
            if (!isset($compare[$key])) {
                continue;
            }

            if (!is_null($value)) {
                switch ($compare[$key]['type']) {
                    case DbTypes::STRING:
                        if (!is_string($value)) {
                            $value = strval($value);
                        } else {
                            $value = trim($value);
                        }

                        break;
                    case DbTypes::INT:
                        if (!is_int($value)) {
                            $value = intval($value);
                        }

                        break;
                    case DbTypes::FLOAT:
                        if (!is_float($value)) {
                            $value = floatval($value);
                        }

                        break;
                    case DbTypes::BOOL:
                        if (!is_bool($value)) {
                            if (is_string($value)) {
                                if ($value === 'true') {
                                    $value = true;
                                } elseif ($value === '1') {
                                    $value = true;
                                } elseif ($value === 'false') {
                                    $value = false;
                                } elseif ($value === '0') {
                                    $value = false;
                                } else {
                                    $value = boolval($value);
                                }
                            } elseif (is_int($value)) {
                                if ($value === 1) {
                                    $value = true;
                                } elseif ($value === 0) {
                                    $value = false;
                                } else {
                                    $value = boolval($value);
                                }
                            } elseif (is_array($value)) {
                                $value = count($value) > 0;
                            } else {
                                $value = boolval($value);
                            }
                        }

                        break;
                    case DbTypes::JSON:
                        $value = formatJsonToSQL($value);

                        break;
                }
            }

            $data[$key] = $value;
        }

        if (!$data) {
            return null;
        }

        return $data;
    }
}

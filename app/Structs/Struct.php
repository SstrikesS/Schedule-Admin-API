<?php

namespace App\Structs;

use ReflectionClass;
use ReflectionProperty;

class Struct
{
    /**
     * Các name phải thuộc filter
     */
    public const OPT_FILTER = 'filter';

    /**
     * Các name sẽ bỏ qua
     */
    public const OPT_IGNORE = 'ignore';

    /**
     * Thay đổi các giá trị của name
     */
    public const OPT_CHANGE = 'change';

    /**
     * Mở rộng thêm dữ liệu
     */
    public const OPT_EXTRA = 'extra';

    public function toArray(?array $opts = null): array
    {
        $reflect = new ReflectionClass($this);

        $obj = [];

        foreach ($reflect->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            if ($opts) {
                if (isset($opts['filter'])) {
                    if (!in_array($property->name, $opts['filter'])) {
                        continue;
                    }
                }

                if (isset($opts['ignore'])) {
                    if (in_array($property->name, $opts['ignore'])) {
                        continue;
                    }
                }

                if (isset($opts['change'][$property->name])) {
                    $change_value = $opts['change'][$property->name];

                    if (is_array($change_value)) {
                        $cv_method = $change_value[0];
                        $cv_args   = $change_value[1] ?? [];

                        if (method_exists($this, $cv_method)) {
                            if (!is_array($cv_args)) {
                                $cv_args = [$cv_args];
                            }

                            $obj[$property->name] = call_user_func_array([$this, $cv_method], $cv_args);

                            continue;
                        }
                    }

                    $obj[$property->name] = $change_value;

                    continue;
                }
            }

            $obj[$property->name] = $this->{$property->name};
        }

        if ($opts && isset($opts['extra'])) {
            $obj = [
                ...$obj,
                ...$opts['extra']
            ];
        }

        return rmArrayObjectByValue($obj);
    }
}

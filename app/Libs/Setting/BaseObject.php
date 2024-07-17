<?php

namespace App\Libs\Setting;

class BaseObject
{
    public function get($property, $default = null)
    {
        $exp = explode('.', $property);

        if (isset($this->{$exp[0]})) {
            $tmp = $default;

            $default = $this->{$exp[0]};

            if (isset($exp[1])) {
                unset($exp[0]);

                foreach ($exp as $item) {
                    if (isset($default[$item])) {
                        $default = $default[$item];
                    } else {
                        $default = $tmp;

                        break;
                    }
                }
            }
        }

        return $default;
    }

    public function getProperties($public = true): array
    {
        $vars = get_object_vars($this);

        if ($public) {
            foreach ($vars as $key => $value) {
                if (str_starts_with($key, '_')) {
                    unset($vars[$key]);
                }
            }
        }

        return $vars;
    }

    public function has($property): bool
    {
        return isset($this->$property);
    }

    public function set($property, $value = null)
    {
        $previous = $this->$property ?? null;

        $this->$property = $value;

        return $previous;
    }

    public function setProperties($properties): bool
    {
        if (is_array($properties) || is_object($properties)) {
            foreach ((array)$properties as $k => $v) {
                // Use the set function which might be overridden.
                $this->set($k, $v);
            }

            return true;
        }

        return false;
    }
}

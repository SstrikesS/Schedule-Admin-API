<?php

namespace App\Libs;

use Illuminate\Http\Request;
use stdClass;

/**
 * Lọc dữ liệu query phục vụ cho
 * - Select dữ liệu trong database theo chuẩn db fields
 * - Check dữ liệu mà client muốn lấy
 */
class QueryFields extends stdClass
{
    public array $select = [];
    public array $more = [];
    public array $all = [];

    protected bool $is_fields;

    public function __construct(Request $request, array $compare, ?array $opts = null)
    {
        if (!$fields = $request->query('fields')) {
            $this->select = array_keys($compare);
            $this->all = $this->select;
        } else {
            if (is_string($fields)) {
                $fields = explode(',', preg_replace('/\s+/', '', $fields));
            }

            if ($opts) {
                /**
                 * Các trường mở rộng sẽ cần phải select thêm dữ liệu
                 * - nếu xuất hiện trường mở rộng mà không có column thì sẽ thêm
                 */
                if (isset($opts['depend'])) {
                    foreach ($opts['depend'] as $key => $value) {
                        if (in_array($key, $fields) && !in_array($value, $fields)) {
                            $fields = [...$fields, $value];
                        }
                    }
                }
            }

            $key_compare = array_keys($compare);

            foreach ($fields as $field) {
                if (in_array($field, $key_compare)) {
                    $this->select[] = $field;
                } else {
                    $this->more[] = $field;
                }

                $this->all[] = $field;
            }
        }

        $this->is_fields = $request->has('fields');
    }

    public function addFilter(string $key): void
    {
        $this->select = [...$this->select, $key];
    }

    public function checkAll(string $key): bool
    {
        if (!$this->all || !$this->is_fields) {
            return true;
        }

        return in_array($key, $this->all);
    }
}
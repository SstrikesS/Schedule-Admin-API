<?php

namespace App\Structs\V1\Admin;

use App\Libs\Serializer\Normalize;
use App\Models\V1\Admin\AUser;
use App\Models\V1\Image\ImageModel;
use App\Structs\Struct;
use Carbon\Carbon;

/*use App\Models\V1\ImageModel;*/

class AUserStruct extends Struct
{
    public ?string $id;
    public ?string $name;
    public ?string $username;
    public ?string $email;
    public ?bool   $status;
    public ?string $phone;
    public ?string $image;
    public ?string $address;

    public ?Carbon $created_at;
    public ?Carbon $email_verified_at;

    public function __construct(object|array $data)
    {
        if (is_object($data)) {
            $data = (array)$data;
        }

        $this->id                = Normalize::initString($data, 'id');
        $this->name              = Normalize::initString($data, 'name');
        $this->username          = Normalize::initString($data, 'username');
        $this->email             = Normalize::initString($data, 'email');
        $this->status            = Normalize::initBool($data, 'status');
        $this->created_at        = Normalize::initCarbon($data, 'created_at');
        $this->phone             = Normalize::initString($data, 'phone');
        $this->image             = Normalize::initString($data, 'image');
        $this->address           = Normalize::initString($data, 'address');
        $this->email_verified_at = Normalize::initCarbon($data,'email_verified_at');
    }

    public function createdAtFormatted(string $format = "d-m-Y H:i:s"): ?string
    {
        if ($format == 'iso') {
            return $this->created_at?->toISOString();
        }

        return $this->created_at?->format($format);
    }


    public function hasPermission(string $permission): bool
    {
        [$func, $action] = explode('-', $permission);

        $user_permissions = AUser::getPermission($this->id);
        $permissions      = json_decode($user_permissions?->permission, true);

        $check_permission = $permissions
            && in_array($func, array_keys($permissions))
            && in_array($action, $permissions[$func]);

        if ($check_permission) {
            return true;
        }

        return false;
    }

    public function getImage(): string
    {
        if ($this->image && ImageModel::isFile($this->image)) {
            return ImageModel::resize($this->image, 200, 200);
        } else {
            return ImageModel::noAvatar();
        }
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}

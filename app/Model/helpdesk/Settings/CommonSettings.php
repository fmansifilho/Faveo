<?php

namespace App\Model\helpdesk\Settings;

use App\BaseModel;

class CommonSettings extends BaseModel
{
    protected $table = 'common_settings';
    protected $fillable = [
        'status', 'option_name', 'option_value', 'optional_field', 'created_at', 'updated_at',
    ];

    public function getStatus($option_name)
    {
        $status = '';
        $schema = $this->where('option_name', $option_name)->first();
        if ($schema) {
            $status = $schema->status;
        }

        return $status;
    }

    public function getOptionValue($option, $field = '', $option_value = false)
    {
        $value = '';
        $schema = $this->where('option_name', $option);
        if ($field != '') {
            $schema = $schema->where('optional_field', $field);

            $value = $schema->first();
        }
        if ($value && $option_value) {
            $value = $value->option_value;
        }

        if (!$value && !$option_value) {
            $value = $schema->get();
        }

        return $value;
    }
}

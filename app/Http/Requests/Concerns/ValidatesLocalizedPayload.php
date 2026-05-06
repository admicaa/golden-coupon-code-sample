<?php

namespace App\Http\Requests\Concerns;

trait ValidatesLocalizedPayload
{
    protected function validateAllowedLanguageKeys($validator, array $payload, $root)
    {
        $unknown = array_diff(array_keys($payload), language_shortcuts());

        foreach ($unknown as $shortcut) {
            $validator->errors()->add($root . '.' . $shortcut, 'The selected ' . $root . '.' . $shortcut . ' is invalid.');
        }
    }
}

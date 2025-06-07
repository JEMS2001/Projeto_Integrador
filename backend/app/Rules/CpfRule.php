<?php

declare(strict_types=1);

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

final class CpfRule implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        // Remove non-numeric characters
        $cpf = preg_replace('/[^0-9]/', '', (string) $value);

        // Check if it has 11 digits
        if (strlen($cpf) != 11) {
            return false;
        }

        // Check if all digits are the same (e.g., 000.000.000-00)
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Calculate the verifier digits
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += (int) $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'O CPF fornecido não é válido.';
    }
}

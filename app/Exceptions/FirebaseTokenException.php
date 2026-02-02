<?php

namespace App\Exceptions;

use Exception;

class FirebaseTokenException extends Exception
{
    public function render()
    {
        return response()->json([
            'message' => $this->message,
            'status' => 'invalid_token',
        ], 401);
    }
}

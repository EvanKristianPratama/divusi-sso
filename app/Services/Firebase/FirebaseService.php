<?php

namespace App\Services\Firebase;

use Kreait\Firebase\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use App\Exceptions\FirebaseTokenException;

class FirebaseService
{
    public function __construct(protected FirebaseAuth $auth)
    {
    }

    /**
     * Verify Firebase ID Token
     *
     * @param string $idToken
     * @return array
     * @throws FirebaseTokenException
     */
    public function verifyToken(string $idToken): array
    {
        try {
            $verifiedIdToken = $this->auth->verifyIdToken($idToken);

            return [
                'firebase_uid' => $verifiedIdToken->claims()->get('sub'),
                'email' => $verifiedIdToken->claims()->get('email'),
                'name' => $verifiedIdToken->claims()->get('name'),
            ];
        } catch (FailedToVerifyToken $e) {
            throw new FirebaseTokenException('Token tidak valid: ' . $e->getMessage());
        }
    }
}

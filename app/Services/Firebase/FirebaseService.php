<?php

namespace App\Services\Firebase;

use App\Exceptions\FirebaseTokenException;
use Kreait\Firebase\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;

/**
 * Service untuk handle Firebase Authentication
 * Single Responsibility: Hanya untuk verify token Firebase
 */
class FirebaseService
{
    public function __construct(
        private FirebaseAuth $auth
    ) {}

    /**
     * Verify Firebase ID Token dan return user data
     *
     * @throws FirebaseTokenException
     */
    public function verifyToken(string $idToken): array
    {
        try {
            $verified = $this->auth->verifyIdToken($idToken);
            $claims = $verified->claims();

            return [
                'firebase_uid' => $claims->get('sub'),
                'email' => $claims->get('email'),
                'name' => $claims->get('name') ?? $claims->get('email'),
            ];
        } catch (FailedToVerifyToken $e) {
            throw new FirebaseTokenException("Token tidak valid: {$e->getMessage()}");
        }
    }
}

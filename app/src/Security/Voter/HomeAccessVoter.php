<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use \App\Entity\User;

final class HomeAccessVoter extends Voter
{
    public const LOGGED_IN = 'HOME_LOGGED_IN';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::LOGGED_IN]);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof User) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {

            case self::LOGGED_IN:
                // at this point, we know the user is logged in
                return true;
                break;
        }

        return false;
    }
}

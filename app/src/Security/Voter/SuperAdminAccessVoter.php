<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class SuperAdminAccessVoter extends Voter
{
    
    protected function supports(string $attribute, mixed $subject): bool
    {
        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // if the user has the role 'ROLE_SUPER_ADMIN', grant access: allow allways voting true faor all actions if the user is super admin
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            return true;
        }

        return false;
    }
}

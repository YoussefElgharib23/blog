<?php

namespace App\Security;


use App\Entity\Post;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PostVoter extends Voter
{
    const EDIT = 'edit';
    const DELETE = 'delete';

    protected function supports(string $attribute, $subject)
    {
        if ( !in_array($attribute, [self::DELETE, self::EDIT]) ) return false;
        
        if ( !$subject instanceof  Post) return false;
        
        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if ( !$user instanceof User ) return false;

        switch ($attribute) {
            case self::EDIT or self::DELETE:
                return $this->hasRoleAdmin($user);
            default:
                return false;
        }
    }

    private function hasRoleAdmin(User $user)
    {
        return in_array('ROLE_ADMIN', $user->getRoles());
    }
}
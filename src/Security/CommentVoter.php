<?php

namespace App\Security;

use App\Entity\Comment;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CommentVoter extends Voter
{
    const DELETE_COMMENT = 'deleteComment';

    protected function supports(string $attribute, $subject)
    {
        if ( !in_array('deleteComment', [self::DELETE_COMMENT]) ) return false;

        if ( !$subject instanceof Comment) return false;

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if ( !$user ) return false;

        if ( !$user instanceof User ) return false;

        return $this->hasRoleAdmin($user);
    }

    private function hasRoleAdmin(User $user) {
        return in_array('ROLE_ADMIN', $user->getRoles());
    }
}
<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class UserSuspensionVoter extends Voter
{
    const ACTION = [
        'suspend',
        'delete'
    ];
    /**
     * @var Security
     */
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject)
    {
        if ( !in_array( $attribute, self::ACTION ) ) return false;

        if ( !$subject instanceof User ) return false;

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if ( !$user instanceof User) return false;

        switch ( $attribute ) {
            default:
                return $this->hasRoleAdmin($subject);
        }
    }

    /**
     * CHECK IF THE USER HAS THE ADMIN ROLE
     *
     * @param $subject
     * @return bool
     */
    private function hasRoleAdmin($subject)
    {
        return
            $this->security->isGranted('ROLE_ADMIN')
            AND $this->security->getUser() !== $subject
            AND $subject->getStatus() !== 'suspended'
            OR $subject->getStatus() !== 'deleted';
    }
}
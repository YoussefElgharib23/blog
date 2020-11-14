<?php

namespace App\Security;

use App\Entity\Notification;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class NotificationVoter extends Voter
{
    /**
     * @var Security
     */
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    const DELETE = 'delete';

    protected function supports(string $attribute, $subject)
    {
        if (!in_array($attribute, [self::DELETE])) {
            return false;
        }

        if ( !$subject instanceof Notification) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if ( !$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::DELETE:
                return $this->hasTheAccess($user);
        }
    }

    private function hasTheAccess(User $user)
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }
}
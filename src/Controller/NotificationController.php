<?php

namespace App\Controller;

use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class NotificationController
 * @package App\Controller
 * @Route("/admin/notifcations", name="app_admin_notifications_")
 * @IsGranted("ROLE_ADMIN")
 */
class NotificationController extends AbstractController
{
    /**
     * @var NotificationRepository
     */
    private NotificationRepository $notificationRepository;
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;
    /**
     * @var FlashyNotifier
     */
    private FlashyNotifier $flashyNotifier;

    public function __construct(FlashyNotifier $flashyNotifier, NotificationRepository $notificationRepository, EntityManagerInterface $entityManager)
    {
        $this->notificationRepository = $notificationRepository;
        $this->entityManager = $entityManager;
        $this->flashyNotifier = $flashyNotifier;
    }

    /**
     * @Route("/", name="index", methods={"GET"})
     */
    public function index()
    {
        $notifications = $this->notificationRepository->findBy([], ['id' => 'DESC']);
        $todayNotifications = [];
        $previousNotifications = [];
        foreach ($notifications as $notification) {
            if (FALSE === $notification->getIsViewed()) {
                $notification->setIsViewed(true);
                $this->entityManager->persist($notification);
            }


            if (date_format($notification->getCreatedAt(), 'd') === date('d')) $todayNotifications[] = $notification;
            else $previousNotifications[] = $notification;
        }

        $this->entityManager->flush();
        return $this->render('admin/notifications.html.twig', [
            'todayNotifications' => $todayNotifications,
            'previousNotifications' => $previousNotifications
        ]);
    }

    /**
     *
     * @Route("/deleteAll", name="delete_all", methods={"DELETE"})
     * @param Request $request
     * @return RedirectResponse
     */
    public function deleteAll(Request $request)
    {
        if ($request->isMethod('DELETE') AND $this->isCsrfTokenValid('app_notifications_delete', $request->request->get('_token'))) {
            $notifications = $this->notificationRepository->findAll();

            foreach($notifications as $notification) {
                $this->denyAccessUnlessGranted('delete', $notification);
                $this->entityManager->remove($notification);
            }
            $this->entityManager->flush();


            $this->flashyNotifier->success('The notifications were deleted !');
        }
        return $this->redirectToRoute('app_admin_notifications_index');
    }
}

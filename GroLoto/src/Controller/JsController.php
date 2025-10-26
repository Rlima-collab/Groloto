// src/Controller/JsController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class JsController extends AbstractController
{
    #[Route('/js/notifications.js', name: 'app_js_notifications')]
    public function notificationsJs(): Response
    {
        return $this->render('js/notifications.js.twig', [], 'application/javascript');
    }
}
<?php

namespace App\Controller\Admin;

use App\Entity\ContactMessage;
use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[Route('/admin/messages')]
#[IsGranted('ROLE_ADMIN')]
class ContactMessageController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    #[Route('', name: 'admin_messages')]
    public function index(): Response
    {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\Utilisateur) {
            throw $this->createAccessDeniedException();
        }
        $adminEmail = $user->getEmail();

        // Récupérer les messages racines (parent_id IS NULL) où l'admin est soit destinataire, soit expéditeur
        // ET qui ne sont pas masqués pour cet admin
        $messages = $this->em->getRepository(ContactMessage::class)
            ->createQueryBuilder('cm')
            ->where('cm.parent_id IS NULL')
            ->andWhere('cm.destinataire = :email OR cm.email = :email OR cm.destinataire = :roleAdmin')
            ->andWhere('cm.masqueePour IS NULL OR cm.masqueePour NOT LIKE :emailPattern')
            ->setParameter('email', $adminEmail)
            ->setParameter('roleAdmin', 'ROLE_ADMIN')
            ->setParameter('emailPattern', '%' . $adminEmail . '%')
            ->orderBy('cm.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        // Pour chaque message racine, charger la conversation complète et déterminer le rôle
        foreach ($messages as $msg) {
            $msg->conversation = $this->getConversationThread($msg->getId());
            
            // Déterminer le dernier message de la conversation
            if (!empty($msg->conversation)) {
                $lastReply = end($msg->conversation);
                $msg->lastMessage = $lastReply->getMessage();
                $msg->lastMessageDate = $lastReply->getCreatedAt();
            } else {
                $msg->lastMessage = $msg->getMessage();
                $msg->lastMessageDate = $msg->getCreatedAt();
            }
            
            // Déterminer le rôle de l'interlocuteur
            $otherEmail = ($msg->getEmail() === $adminEmail) ? $msg->getDestinataire() : $msg->getEmail();
            $msg->otherUserEmail = $otherEmail;
            $msg->otherUserRole = $this->getUserRole($otherEmail);
            $msg->otherUserProfileImage = $this->getUserProfileImage($otherEmail);
            
            // Déterminer le nom de l'interlocuteur pour l'affichage
            if ($otherEmail === 'ALL_BENEVOLES') {
                $msg->otherUserName = 'Tous les bénévoles';
            } elseif ($otherEmail === 'ALL_MECENES') {
                $msg->otherUserName = 'Tous les mécènes';
            } else {
                $otherUser = $this->em->getRepository(\App\Entity\Utilisateur::class)->findOneBy(['email' => $otherEmail]);
                if ($otherUser) {
                    $msg->otherUserName = trim(($otherUser->getPrenom() ?? '') . ' ' . ($otherUser->getNom() ?? ''));
                } else {
                    $msg->otherUserName = $otherEmail;
                }
            }
        }

        // Compter les bénévoles actifs pour l'interface
        $benevoleCount = $this->em->getRepository(\App\Entity\Benevole::class)->countActive();

        return $this->render('admin/message/messages.html.twig', [
            'messages' => $messages,
            'benevoleCount' => $benevoleCount,
        ]);
    }

    /**
     * Détermine le rôle d'un utilisateur par son email
     */
    private function getUserRole(?string $email): string
    {
        if (!$email) return 'Inconnu';
        if ($email === 'ROLE_ADMIN') return 'Admin';
        
        $user = $this->em->getRepository(\App\Entity\Utilisateur::class)
            ->findOneBy(['email' => $email]);
        
        if (!$user) return 'Externe';
        
        $roles = $user->getRoles();
        if (in_array('ROLE_ADMIN', $roles)) return 'Admin';
        if (in_array('ROLE_MECENE', $roles)) return 'Mécène';
        if (in_array('ROLE_BENEVOLE', $roles)) return 'Bénévole';
        
        return 'Utilisateur';
    }

    /**
     * Récupère l'image de profil d'un utilisateur par son email
     */
    private function getUserProfileImage(?string $email): ?string
    {
        if (!$email) return null;
        
        $user = $this->em->getRepository(\App\Entity\Utilisateur::class)
            ->findOneBy(['email' => $email]);
        
        if (!$user) return null;
        
        return $user->getProfileImage();
    }

    /**
     * Récupère tous les messages d'un fil de discussion
     */
    private function getConversationThread(int $rootMessageId): array
    {
        return $this->em->getRepository(ContactMessage::class)
            ->createQueryBuilder('cm')
            ->where('cm.parent_id = :parentId')
            ->setParameter('parentId', $rootMessageId)
            ->orderBy('cm.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    #[Route('/envoyes', name: 'admin_messages_sent')]
    public function sent(): Response
    {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\Utilisateur) {
            throw $this->createAccessDeniedException();
        }
        $adminEmail = $user->getEmail();

        // Messages envoyés par l'admin (email = admin email, destinataire rempli)
        $messages = $this->em->getRepository(ContactMessage::class)
            ->findBy(['email' => $adminEmail], ['createdAt' => 'DESC']);

        return $this->render('admin/message/messages.html.twig', [
            'messages' => $messages,
            'sent_view' => true,
        ]);
    }

    // POPUP NOUVEAU MESSAGE (AJAX ONLY)
    #[Route('/nouveau', name: 'admin_message_new', methods: ['POST'])]
    public function new(Request $request, MailerInterface $mailer): JsonResponse
    {
        // Sécurité : AJAX uniquement
        if (!$request->isXmlHttpRequest()) {
            return $this->json(['success' => false, 'error' => 'Requête non autorisée'], 403);
        }

        try {
            $to = trim($request->request->get('to'));
            $subject = trim($request->request->get('subject'));
            $message = trim($request->request->get('message'));

            if (!$to || !$message) {
                return $this->json(['success' => false, 'error' => 'Destinataire et message requis'], 400);
            }

            $user = $this->getUser();
            if (!$user instanceof \App\Entity\Utilisateur) {
                return $this->json(['success' => false, 'error' => 'Utilisateur non valide'], 500);
            }

            $fullMessage = $subject ? "[{$subject}] {$message}" : $message;
            
            // Nom de l'expéditeur (Admin)
            $nomExpediteur = trim(($user->getPrenom() ?? '') . ' ' . ($user->getNom() ?? ''));
            if (empty($nomExpediteur)) {
                $nomExpediteur = $user->getEmail();
            }

            // Supporter l'envoi massif à des groupes spéciaux : ALL_BENEVOLES / ALL_MECENES
            $lowerTo = mb_strtolower($to);
            if (in_array($lowerTo, ['all_benevoles', 'all_bénévoles', 'tous_les_benevoles', 'tous', 'tous_les_bénévoles'])) {
                // Envoyer à tous les bénévoles actifs
                $benevoles = $this->em->getRepository(\App\Entity\Benevole::class)->findActiveWithUser();

                if (empty($benevoles)) {
                    return $this->json(['success' => false, 'error' => 'Aucun bénévole actif trouvé'], 400);
                }

                // Créer UN SEUL message de groupe pour l'admin
                $cm = new ContactMessage();
                $cm->setNom($nomExpediteur)
                    ->setEmail($user->getEmail())
                    ->setDestinataire('ALL_BENEVOLES') // Marqueur spécial
                    ->setMessage($fullMessage);
                $this->em->persist($cm);

                $sent = 0;
                foreach ($benevoles as $benevole) {
                    $destUser = $benevole->getUtilisateur();
                    if (!$destUser) continue;

                    try {
                        $destEmail = $destUser->getEmail();
                        
                        // Envoi mail
                        $emailObj = (new Email())
                            ->from('contact@groloto.com')
                            ->to($destEmail)
                            ->subject($subject ?: 'Message depuis Groloto')
                            ->html(nl2br(htmlspecialchars($message)));
                        $mailer->send($emailObj);

                        // Notification
                        try {
                            $notif = new Notification();
                            $notif->setDestinataire($destUser)
                                ->setType('contact')
                                ->setMessage("Vous avez reçu un message de l'admin")
                                ->setLien('/benevole/messages') // Redirige vers la messagerie où ils verront le message de groupe
                                ->setLue(false)
                                ->setCreatedAt(new \DateTime());
                            $this->em->persist($notif);
                        } catch (\Exception $e) {
                            // ignore
                        }

                        $sent++;
                    } catch (\Exception $e) {
                        // ignorer les erreurs individuelles
                        continue;
                    }
                }

                $this->em->flush();

                return $this->json([
                    'success' => true, 
                    'message' => sprintf('%d messages envoyés aux bénévoles', $sent), 
                    'sentCount' => $sent,
                    'conversationId' => $cm->getId() // Retourner l'ID de la conversation de groupe
                ]);
            }

            if (in_array($lowerTo, ['all_mecenes', 'tous_les_mecenes', 'tous_mecenes'])) {
                // Envoyer à tous les mécènes
                $mecenes = $this->em->getRepository(\App\Entity\Utilisateur::class)
                    ->createQueryBuilder('u')
                    ->join('u.role', 'r')
                    ->where('r.nom = :role')
                    ->setParameter('role', 'mecene')
                    ->getQuery()
                    ->getResult();

                if (empty($mecenes)) {
                    return $this->json(['success' => false, 'error' => 'Aucun mécène trouvé'], 400);
                }

                // Créer UN SEUL message de groupe pour l'admin
                $cm = new ContactMessage();
                $cm->setNom($nomExpediteur)
                    ->setEmail($user->getEmail())
                    ->setDestinataire('ALL_MECENES') // Marqueur spécial
                    ->setMessage($fullMessage);
                $this->em->persist($cm);

                $sent = 0;
                foreach ($mecenes as $destUser) {
                    try {
                        $destEmail = $destUser->getEmail();

                        // Envoi mail
                        $emailObj = (new Email())
                            ->from('contact@groloto.com')
                            ->to($destEmail)
                            ->subject($subject ?: 'Message depuis Groloto')
                            ->html(nl2br(htmlspecialchars($message)));
                        $mailer->send($emailObj);

                        // Notification
                        try {
                            $notif = new Notification();
                            $notif->setDestinataire($destUser)
                                ->setType('contact')
                                ->setMessage("Vous avez reçu un message de l'admin")
                                ->setLien('/mecene/messages')
                                ->setLue(false)
                                ->setCreatedAt(new \DateTime());
                            $this->em->persist($notif);
                        } catch (\Exception $e) {
                            // ignore
                        }

                        $sent++;
                    } catch (\Exception $e) {
                        continue;
                    }
                }

                $this->em->flush();

                return $this->json([
                    'success' => true, 
                    'message' => sprintf('%d messages envoyés aux mécènes', $sent), 
                    'sentCount' => $sent,
                    'conversationId' => $cm->getId() // Retourner l'ID de la conversation de groupe
                ]);
            }

            // Vérifier que le destinataire existe dans la base de données (flux normal)
            $destinataire = $this->em->getRepository(\App\Entity\Utilisateur::class)
                ->findOneBy(['email' => $to]);
            
            if (!$destinataire) {
                return $this->json(['success' => false, 'error' => 'Le destinataire n\'est pas enregistré sur le site'], 400);
            }

            // Enregistrer le message dans la base et envoyer un email unique
            $cm = new ContactMessage();
            $cm->setNom($nomExpediteur)
                ->setEmail($user->getEmail())
                ->setDestinataire($to)
                ->setMessage($fullMessage);
            
            $this->em->persist($cm);
            $this->em->flush();

            try {
                $email = (new Email())
                    ->from('contact@groloto.com')
                    ->to($to)
                    ->subject($subject ?: 'Message depuis Groloto')
                    ->html(nl2br(htmlspecialchars($message)));

                $mailer->send($email);

                // créer une notification pour l'utilisateur destinataire si trouvé
                try {
                    $userDest = $this->em->getRepository(\App\Entity\Utilisateur::class)->findOneByEmail($to);
                    if ($userDest) {
                        $roleName = strtolower($userDest->getRole()?->getNom() ?? '');
                        $lien = '/';
                        if ($roleName === 'benevole') {
                            $lien = '/benevole/messages?conv=' . $cm->getId();
                        } elseif ($roleName === 'mecene') {
                            $lien = '/mecene/messages?conv=' . $cm->getId();
                        }
                        
                        $notif = new Notification();
                        $notif->setDestinataire($userDest)
                            ->setType('contact')
                            ->setMessage("Vous avez reçu un message de l'admin")
                            ->setLien($lien)
                            ->setLue(false)
                            ->setCreatedAt(new \DateTime());
                        $this->em->persist($notif);
                        $this->em->flush();
                    }
                } catch (\Exception $e) {
                    // ignore notification failures
                }

                return $this->json([
                    'success' => true, 
                    'message' => 'Message envoyé !',
                    'conversationId' => $cm->getId()
                ]);
            } catch (\Exception $e) {
                return $this->json(['success' => false, 'error' => 'Échec d\'envoi'], 500);
            }
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/{id}/repondre', name: 'admin_message_reply', methods: ['POST'])]
    public function reply(
        Request $request,
        ContactMessage $message,
        EntityManagerInterface $em,
        MailerInterface $mailer
    ): Response {
        // Vérifier si c'est une requête AJAX
        $isAjax = $request->isXmlHttpRequest();
        
        $currentUser = $this->getUser();
        if (!$currentUser instanceof \App\Entity\Utilisateur) {
            if ($isAjax) {
                return $this->json(['success' => false, 'error' => 'Utilisateur non valide'], 403);
            }
            throw $this->createAccessDeniedException();
        }
        $currentEmail = $currentUser->getEmail();

        // Vérifier si la conversation est masquée par l'autre utilisateur
        $otherEmail = ($message->getEmail() === $currentEmail) ? $message->getDestinataire() : $message->getEmail();
        if ($message->isMasqueePour($otherEmail)) {
            if ($isAjax) {
                return $this->json(['success' => false, 'error' => 'Cette conversation a été supprimée par l\'autre utilisateur.'], 410);
            }
            $this->addFlash('error', 'Cette conversation a été supprimée par l\'autre utilisateur.');
            return $this->redirectToRoute('admin_messages');
        }

        // Vérifier si la conversation est clôturée
        if ($message->isCloturee()) {
            if ($isAjax) {
                return $this->json(['success' => false, 'error' => 'Cette conversation est clôturée.'], 403);
            }
            $this->addFlash('error', 'Cette conversation est clôturée.');
            return $this->redirectToRoute('admin_messages');
        }

        // Vérifier si un autre admin a déjà répondu
        if ($message->getReponduPar() !== null && $message->getReponduPar() !== $currentUser) {
            $repondeur = $message->getReponduPar();
            $nomRepondeur = trim(($repondeur->getPrenom() ?? '') . ' ' . ($repondeur->getNom() ?? ''));
            if (empty($nomRepondeur)) {
                $nomRepondeur = $repondeur->getEmail();
            }
            
            if ($isAjax) {
                return $this->json(['success' => false, 'error' => "Ce message est pris en charge par {$nomRepondeur}."], 409);
            }
            $this->addFlash('error', "Ce message est pris en charge par {$nomRepondeur}.");
            return $this->redirectToRoute('admin_messages');
        }

        $reponse = trim($request->request->get('reponse'));
        if (!$reponse) {
            if ($isAjax) {
                return $this->json(['success' => false, 'error' => 'La réponse ne peut pas être vide.'], 400);
            }
            $this->addFlash('error', 'La réponse ne peut pas être vide.');
            return $this->redirectToRoute('admin_messages');
        }

        $message
            ->setReponse($reponse)
            ->setReponduPar($this->getUser())
            ->setReponduLe(new \DateTime())
            ->setLu(true);
        $em->flush();

        // 1. Marquer les notifications existantes comme lues
        $notifications = $em->getRepository(Notification::class)
            ->createQueryBuilder('n')
            ->where('n.destinataire = :user')
            ->andWhere('n.type = :type')
            ->andWhere('n.lue = false')
            ->setParameter('user', $this->getUser())
            ->setParameter('type', 'contact')
            ->getQuery()
            ->getResult();

        foreach ($notifications as $notif) {
            $notif->setLue(true);
        }

        // 2. Récupérer l'expéditeur du message pour la notification plus tard
        $expediteur = $em->getRepository(\App\Entity\Utilisateur::class)
            ->findOneByEmail($message->getEmail());

        $em->flush();

        // 3. Envoi de la réponse par email
        $email = (new Email())
            ->from('contact@groloto.com')
            ->to($message->getEmail())
            ->subject('Re: ' . $this->extractSubject($message->getMessage()))
            ->html("
                <h3>Bonjour {$message->getNom()},</h3>
                <p>Merci pour votre message :</p>
                <blockquote style='border-left:3px solid #ccc; padding-left:10px; margin: 10px 0;'>
                    <em>{$message->getMessage()}</em>
                </blockquote>
                <hr>
                <p><strong>Notre réponse :</strong></p>
                <p>" . nl2br(htmlspecialchars($reponse)) . "</p>
                <p>Vous pouvez répondre à ce message via le formulaire de contact sur notre site.</p>
                <p>Cordialement,<br>L'équipe Groloto</p>
            ");

        $mailer->send($email);
        // 4. Créer un message destiné à l'utilisateur pour qu'il apparaisse dans sa boîte de réception
        try {
            $admin = $currentUser;
            $replyFull = $reponse; // Retirer le préfixe [Reponse admin]
            
            // Déterminer le parent_id : si le message actuel a déjà un parent, on utilise ce parent, sinon on utilise l'ID du message actuel
            $rootParentId = $message->getParentId() ?? $message->getId();
            
            $cmReply = new ContactMessage();
            $cmReply->setNom(trim(($admin->getPrenom() ?? '') . ' ' . ($admin->getNom() ?? '')))
                ->setEmail($admin->getEmail())
                ->setDestinataire($message->getEmail())
                ->setMessage($replyFull)
                ->setParentId($rootParentId); // Lien vers le message racine
            $em->persist($cmReply);
            $em->flush();

            // si l'expéditeur existe en BDD, créer une notification pointant vers sa messagerie
            if ($expediteur) {
                $roleName = strtolower($expediteur->getRole()?->getNom() ?? '');
                $lien = '/';
                if ($roleName === 'benevole') {
                    $lien = '/benevole/messages?conv=' . $rootParentId;
                } elseif ($roleName === 'mecene') {
                    $lien = '/mecene/messages?conv=' . $rootParentId;
                }
                
                $notif2 = new Notification();
                $notif2->setDestinataire($expediteur)
                    ->setType('reponse_contact')
                    ->setMessage('Vous avez reçu une réponse d\'un admin')
                    ->setLien($lien)
                    ->setLue(false)
                    ->setCreatedAt(new \DateTime());
                $em->persist($notif2);
                $em->flush();
            }
        } catch (\Exception $e) {
            // ne pas bloquer si la persistance échoue
        }

        if ($isAjax) {
            return $this->json([
                'success' => true,
                'message' => 'Réponse envoyée !',
                'messageId' => $cmReply->getId()
            ]);
        }

        $this->addFlash('success', 'Réponse envoyée !');

        return $this->redirectToRoute('admin_messages');
    }

    #[Route('/{id}/delete-conversation', name: 'admin_message_delete', methods: ['POST'])]
    public function delete(ContactMessage $message, EntityManagerInterface $em, Request $request): Response
    {
        $isAjax = $request->isXmlHttpRequest();
        
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\Utilisateur) {
            if ($isAjax) {
                return $this->json(['success' => false, 'error' => 'Utilisateur non valide'], 403);
            }
            throw $this->createAccessDeniedException();
        }
        
        // Clôturer la conversation avant de la masquer
        $message->setCloturee(true);
        
        // Ne pas supprimer, mais masquer pour l'utilisateur
        $message->masquerPour($user->getEmail());
        $em->flush();
        
        if ($isAjax) {
            return $this->json(['success' => true, 'message' => 'Conversation supprimée']);
        }
        
        $this->addFlash('success', 'Message supprimé.');
        return $this->redirectToRoute('admin_messages');
    }

    #[Route('/{id}/cloturer', name: 'admin_message_close', methods: ['POST'])]
    public function cloturer(ContactMessage $message, Request $request): JsonResponse
    {
        $message->setCloturee(true);
        $this->em->flush();

        return $this->json(['success' => true, 'message' => 'Conversation clôturée']);
    }

    #[Route('/emails-list', name: 'admin_message_emails_list', methods: ['GET'])]
    public function emailsList(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');
        
        $qb = $this->em->getRepository(\App\Entity\Utilisateur::class)
            ->createQueryBuilder('u')
            ->leftJoin('u.role', 'r')
            ->select('u.email', 'u.prenom', 'u.nom', 'r.nom as roleName');
        
        if ($query) {
            $qb->where('u.email LIKE :query')
               ->setParameter('query', '%' . $query . '%');
        }
        
        $users = $qb->setMaxResults(10)
                   ->getQuery()
                   ->getResult();
                   
        // Formater les données pour le JS
        $formattedUsers = array_map(function($user) {
            $roleName = $user['roleName'] ?? 'user';
            $role = match ($roleName) {
                'admin' => 'Admin',
                'mecene' => 'Mécène',
                'benevole' => 'Bénévole',
                default => 'Utilisateur',
            };
            
            return [
                'email' => $user['email'],
                'prenom' => $user['prenom'],
                'nom' => $user['nom'],
                'role' => $role
            ];
        }, $users);
        
        return $this->json($formattedUsers);
    }

    #[Route('/{id}/edit', name: 'admin_message_edit', methods: ['POST'])]
    public function edit(
        ContactMessage $message,
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        // Vérifier que c'est bien le message de l'admin
        $currentUser = $this->getUser();
        if (!$currentUser instanceof \App\Entity\Utilisateur) {
            return $this->json(['success' => false, 'error' => 'Utilisateur non valide'], 403);
        }

        if ($message->getEmail() !== $currentUser->getEmail()) {
            return $this->json(['success' => false, 'error' => 'Vous ne pouvez modifier que vos propres messages'], 403);
        }

        $newContent = trim($request->request->get('message'));
        if (!$newContent) {
            return $this->json(['success' => false, 'error' => 'Le message ne peut pas être vide'], 400);
        }

        // Ajouter un indicateur de modification dans le message
        $originalMessage = $message->getMessage();
        
        // Extraire le sujet s'il existe (format [Sujet])
        $subject = '';
        if (preg_match('/^\[([^\]]+)\]\s*(.*)$/s', $originalMessage, $matches)) {
            $subject = '[' . $matches[1] . '] ';
        }
        
        // Vérifier si le message était déjà modifié
        if (!str_starts_with($originalMessage, '[MODIFIÉ]')) {
            $message->setMessage('[MODIFIÉ] ' . $subject . $newContent);
        } else {
            // Le message était déjà modifié, on remplace juste le contenu
            $message->setMessage('[MODIFIÉ] ' . $subject . $newContent);
        }

        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Message modifié'
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_message_delete_msg', methods: ['POST'])]
    public function deleteMessage(
        ContactMessage $message,
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        // Vérifier que c'est bien le message de l'admin
        $currentUser = $this->getUser();
        if (!$currentUser instanceof \App\Entity\Utilisateur) {
            return $this->json(['success' => false, 'error' => 'Utilisateur non valide'], 403);
        }

        if ($message->getEmail() !== $currentUser->getEmail()) {
            return $this->json(['success' => false, 'error' => 'Vous ne pouvez supprimer que vos propres messages'], 403);
        }

        // Marquer le message comme supprimé au lieu de le supprimer réellement
        $message->setMessage('[SUPPRIMÉ] Ce message a été supprimé par son auteur');
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Message supprimé'
        ]);
    }

    #[Route('/{id}/mark-replied', name: 'admin_message_mark_replied', methods: ['POST'])]
    public function markReplied(
        ContactMessage $message,
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof \App\Entity\Utilisateur) {
            return $this->json(['success' => false, 'error' => 'Utilisateur non valide'], 403);
        }

        $mark = $request->request->get('mark', '1');
        $mark = filter_var($mark, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if ($mark) {
            $message->setReponduPar($currentUser);
            $message->setReponduLe(new \DateTime());
        } else {
            $message->setReponduPar(null);
            $message->setReponduLe(null);
        }

        $em->flush();

        return $this->json(['success' => true, 'marked' => (bool) $mark]);
    }

    private function extractSubject(string $message): string
    {
        if (preg_match('/^\[([^\]]+)\]/', $message, $matches)) {
            return $matches[1];
        }
        return 'Votre message';
    }
}
<?php

namespace App\Tests\Controller;

use App\Controller\DashboardController;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class DashboardControllerTest extends TestCase
{
    private function createUtilisateurWithId(int $id): Utilisateur
    {
        $user = new Utilisateur();
        $ref = new \ReflectionProperty(Utilisateur::class, 'id');
        $ref->setAccessible(true);
        $ref->setValue($user, $id);

        return $user;
    }

    /** @param array<mixed> $result */
    private function createQueryMock(array $result = [], mixed $scalar = null): Query
    {
        $query = $this->createMock(Query::class);
        $query->method('setParameter')->willReturnSelf();
        $query->method('setParameters')->willReturnSelf();
        $query->method('setMaxResults')->willReturnSelf();
        $query->method('getResult')->willReturn($result);
        $query->method('getSingleScalarResult')->willReturn($scalar);

        return $query;
    }

    public function testControllerExists(): void
    {
        $this->assertTrue(class_exists('App\Controller\DashboardController'));
    }

    public function testIndexMethodExists(): void
    {
        $this->assertTrue(method_exists('App\Controller\DashboardController', 'index'));
    }

    public function testIndexRendersPublicDashboardWhenNoUser(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('createQuery')->willReturnCallback(function (string $dql): Query {
            if (str_contains($dql, 'SELECT e FROM App\Entity\Evenement e')) {
                return $this->createQueryMock(result: []);
            }
            if (str_contains($dql, 'SELECT COUNT(e.id)')) {
                return $this->createQueryMock(scalar: 0);
            }
            if (str_contains($dql, 'COUNT(b.id)')) {
                return $this->createQueryMock(scalar: 0);
            }
            if (str_contains($dql, 'COUNT(m.id)')) {
                return $this->createQueryMock(scalar: 0);
            }

            return $this->createQueryMock(scalar: 0);
        });

        $controller = new TestableDashboardController($entityManager);
        $controller->setUser(null);

        $response = $controller->index();

        $this->assertSame('dashboard_public.html.twig', $controller->lastTemplate);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testIndexRendersAuthenticatedDashboardForAdmin(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('createQuery')->willReturnCallback(function (string $dql): Query {
            if (str_contains($dql, 'App\Entity\Stock')) {
                throw new \RuntimeException('Stock not available');
            }
            if (str_contains($dql, 'SELECT e FROM App\Entity\Evenement e')) {
                return $this->createQueryMock(result: []);
            }
            return $this->createQueryMock(scalar: 0);
        });

        $controller = new TestableDashboardController($entityManager);
        $controller->setUser($this->createUtilisateurWithId(1));
        $controller->setGranted(['ROLE_ADMIN' => true]);

        $controller->index();

        $this->assertSame('dashboard.html.twig', $controller->lastTemplate);
        $this->assertArrayHasKey('role_data', $controller->lastParameters);
        $this->assertSame(['total_users' => 0], $controller->lastParameters['role_data']);
    }

    public function testIndexRendersAuthenticatedDashboardForMecene(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('createQuery')->willReturnCallback(function (string $dql): Query {
            if (str_contains($dql, 'App\Entity\Stock')) {
                throw new \RuntimeException('Stock not available');
            }
            if (str_contains($dql, 'COALESCE(SUM')) {
                return $this->createQueryMock(scalar: 0);
            }
            if (str_contains($dql, 'SELECT e FROM App\Entity\Evenement e')) {
                return $this->createQueryMock(result: []);
            }
            return $this->createQueryMock(scalar: 0);
        });

        $controller = new TestableDashboardController($entityManager);
        $controller->setUser($this->createUtilisateurWithId(42));
        $controller->setGranted(['ROLE_MECENE' => true]);

        $controller->index();

        $this->assertSame('dashboard.html.twig', $controller->lastTemplate);
        $this->assertSame(['sponsor_contributions' => 0], $controller->lastParameters['role_data']);
    }

    public function testIndexRendersAuthenticatedDashboardForBenevoleWithTacheFallback(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('createQuery')->willReturnCallback(function (string $dql): Query {
            if (str_contains($dql, 'App\Entity\Stock')) {
                throw new \RuntimeException('Stock not available');
            }
            if (str_contains($dql, 'App\Entity\Tache')) {
                throw new \RuntimeException('Tache not available');
            }
            if (str_contains($dql, 'SELECT e FROM App\Entity\Evenement e')) {
                return $this->createQueryMock(result: []);
            }
            return $this->createQueryMock(scalar: 0);
        });

        $controller = new TestableDashboardController($entityManager);
        $controller->setUser($this->createUtilisateurWithId(7));
        $controller->setGranted(['ROLE_BENEVOLE' => true]);

        $controller->index();

        $this->assertSame('dashboard.html.twig', $controller->lastTemplate);
        $this->assertSame(['pending_tasks' => []], $controller->lastParameters['role_data']);
    }

    public function testIndexRendersAuthenticatedDashboardForOtherRole(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('createQuery')->willReturnCallback(function (string $dql): Query {
            if (str_contains($dql, 'App\Entity\Stock')) {
                throw new \RuntimeException('Stock not available');
            }
            if (str_contains($dql, 'SELECT e FROM App\Entity\Evenement e')) {
                return $this->createQueryMock(result: []);
            }
            return $this->createQueryMock(scalar: 0);
        });

        $controller = new TestableDashboardController($entityManager);
        $controller->setUser($this->createUtilisateurWithId(99));
        $controller->setGranted([]);

        $controller->index();

        $this->assertSame('dashboard.html.twig', $controller->lastTemplate);
        $this->assertSame(['public_events' => []], $controller->lastParameters['role_data']);
    }
}

final class TestableDashboardController extends DashboardController
{
    public ?string $lastTemplate = null;
    /** @var array<string,mixed> */
    public array $lastParameters = [];

    private ?UserInterface $user = null;
    /** @var array<string,bool> */
    private array $granted = [];

    public function setUser(?UserInterface $user): void
    {
        $this->user = $user;
    }

    /** @param array<string,bool> $granted */
    public function setGranted(array $granted): void
    {
        $this->granted = $granted;
    }

    protected function getUser(): ?UserInterface
    {
        return $this->user;
    }

    protected function isGranted(mixed $attribute, mixed $subject = null): bool
    {
        return $this->granted[(string) $attribute] ?? false;
    }

    /** @param array<string,mixed> $parameters */
    protected function render(string $view, array $parameters = [], ?Response $response = null): Response
    {
        $this->lastTemplate = $view;
        $this->lastParameters = $parameters;
        return new Response('ok', 200);
    }
}

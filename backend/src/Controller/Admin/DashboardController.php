<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Book;
use App\Entity\Author;
use App\Entity\Publisher;
use App\Entity\Category;
use App\Entity\Borrowing;
use App\Entity\Purchase;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(BookCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Système de Gestion de Bibliothèque')
            ->setLocales(['fr']);
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de Bord', 'fa fa-home');
        
        yield MenuItem::section('Gestion de la Bibliothèque');
        yield MenuItem::linkToCrud('Livres', 'fa fa-book', Book::class);
        yield MenuItem::linkToCrud('Auteurs', 'fa fa-user-pen', Author::class);
        yield MenuItem::linkToCrud('Éditeurs', 'fa fa-building', Publisher::class);
        yield MenuItem::linkToCrud('Catégories', 'fa fa-tags', Category::class);
        
        yield MenuItem::section('Transactions');
        yield MenuItem::linkToCrud('Emprunts', 'fa fa-hand-holding', Borrowing::class);
        yield MenuItem::linkToCrud('Achats', 'fa fa-shopping-cart', Purchase::class);
        
        yield MenuItem::section('Gestion des Utilisateurs');
        yield MenuItem::linkToCrud('Utilisateurs', 'fa fa-users', User::class);
        
        yield MenuItem::section('');
        yield MenuItem::linkToLogout('Déconnexion', 'fa fa-sign-out');
    }
}

<?php

namespace App\Controller\Admin;

use App\Entity\Borrowing;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Doctrine\ORM\EntityManagerInterface;

class BorrowingCrudController extends AbstractCrudController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public static function getEntityFqcn(): string
    {
        return Borrowing::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $approveReturn = Action::new('approveReturn', 'Accepter le retour', 'fa fa-check')
            ->linkToUrl(function (Borrowing $borrowing) {
                return $this->generateUrl('admin_approve_return', ['id' => $borrowing->getId()]);
            })
            ->displayIf(function (Borrowing $borrowing) {
                return $borrowing->getStatus() === 'pending_return';
            })
            ->addCssClass('btn btn-success');

        return $actions
            ->add(Crud::PAGE_INDEX, $approveReturn)
            ->add(Crud::PAGE_DETAIL, $approveReturn);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm()->setLabel('ID'),
            AssociationField::new('user')->setLabel('Utilisateur'),
            AssociationField::new('book')->setLabel('Livre'),
            DateTimeField::new('borrowedAt')->hideOnForm()->setLabel('Emprunté le'),
            DateTimeField::new('dueDate')->setLabel('À retourner le'),
            DateTimeField::new('returnedAt')->setLabel('Retourné le')->hideOnForm(),
            ChoiceField::new('status')
                ->setLabel('Statut')
                ->setChoices([
                    'Actif' => 'active',
                    'En attente de retour' => 'pending_return',
                    'Retourné' => 'returned',
                    'En retard' => 'overdue',
                ])
                ->renderAsBadges([
                    'active' => 'primary',
                    'pending_return' => 'warning',
                    'returned' => 'success',
                    'overdue' => 'danger',
                ]),
        ];
    }
}

<?php

namespace App\Controller\Admin;

use App\Entity\Purchase;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;

class PurchaseCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Purchase::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm()->setLabel('ID'),
            AssociationField::new('user')->setLabel('Utilisateur'),
            AssociationField::new('book')->setLabel('Livre'),
            DateTimeField::new('purchasedAt')->hideOnForm()->setLabel('Acheté le'),
            MoneyField::new('price')->setCurrency('TND')->setLabel('Prix (DT)')->setNumDecimals(3),
            IntegerField::new('quantity')->setLabel('Quantité'),
            ChoiceField::new('status')
                ->setLabel('Statut')
                ->setChoices([
                    'En attente' => 'pending',
                    'Complété' => 'completed',
                    'Annulé' => 'cancelled',
                ]),
        ];
    }
}

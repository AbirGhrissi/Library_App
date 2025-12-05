<?php

namespace App\Controller\Admin;

use App\Entity\Publisher;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;

class PublisherCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Publisher::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm()->setLabel('ID'),
            TextField::new('name')->setLabel('Nom'),
            TextField::new('address')->hideOnIndex()->setLabel('Adresse'),
            TextField::new('phone')->setLabel('Téléphone'),
            EmailField::new('email')->setLabel('Email'),
        ];
    }
}

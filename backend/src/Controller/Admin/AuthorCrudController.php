<?php

namespace App\Controller\Admin;

use App\Entity\Author;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;

class AuthorCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Author::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm()->setLabel('ID'),
            TextField::new('name')->setLabel('Nom'),
            TextareaField::new('biography')->hideOnIndex()->setLabel('Biographie'),
            DateField::new('birthDate')->setLabel('Date de naissance'),
            TextField::new('nationality')->setLabel('Nationalité'),
        ];
    }
}

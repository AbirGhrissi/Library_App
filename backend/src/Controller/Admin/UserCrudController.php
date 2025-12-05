<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm()->setLabel('ID'),
            EmailField::new('email')->setLabel('Email'),
            TextField::new('firstName')->setLabel('Prénom'),
            TextField::new('lastName')->setLabel('Nom'),
            TextField::new('phone')->hideOnIndex()->setLabel('Téléphone'),
            ChoiceField::new('roles')
                ->setLabel('Rôle Principal')
                ->setChoices([
                    'Utilisateur' => 'ROLE_USER',
                    'Bibliothécaire' => 'ROLE_LIBRARIAN',
                    'Administrateur' => 'ROLE_ADMIN',
                    'Super Administrateur' => 'ROLE_SUPER_ADMIN',
                ])
                ->allowMultipleChoices()
                ->renderExpanded()
                ->setHelp('La hiérarchie: Super Admin > Admin > Bibliothécaire > Utilisateur. Un rôle supérieur hérite des permissions des rôles inférieurs.'),
            DateTimeField::new('createdAt')->hideOnForm()->setLabel('Créé le'),
        ];
    }
}

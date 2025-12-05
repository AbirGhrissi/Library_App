<?php

namespace App\Controller\Admin;

use App\Entity\Book;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;

class BookCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Book::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm()->setLabel('ID'),
            TextField::new('title')->setLabel('Titre'),
            TextField::new('isbn')->setLabel('ISBN'),
            TextareaField::new('description')->hideOnIndex()->setLabel('Description'),
            AssociationField::new('authors')->setLabel('Auteurs'),
            AssociationField::new('publisher')->setLabel('Éditeur'),
            AssociationField::new('categories')->setLabel('Catégories'),
            DateField::new('publicationDate')->setLabel('Date de publication'),
            MoneyField::new('price')->setCurrency('TND')->setLabel('Prix (DT)')->setNumDecimals(3),
            IntegerField::new('stockQuantity')->setLabel('Quantité en stock'),
            IntegerField::new('borrowableQuantity')->setLabel('Quantité empruntable'),
            ImageField::new('coverImage')
                ->setLabel('Image de couverture')
                ->setBasePath('uploads/covers')
                ->setUploadDir('public/uploads/covers')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->hideOnIndex(),
        ];
    }
}

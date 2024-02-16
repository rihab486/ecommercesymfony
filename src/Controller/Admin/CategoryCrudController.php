<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use DateTime;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;

class CategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name'),
            DateTimeField::new('created_at'),
            SlugField::new('slug')->setTargetFieldName('name'),
            TextEditorField::new('description'),
            BooleanField::new('isMega'),
            ImageField::new('imageUrl')
            ->setBasePath("/assets/images/categories")
            ->setUploadDir("/public/assets/images/categories")
            ->setUploadedFileNamePattern('[randomhash].[extension]'),
          //  ->setRequired(false),
            
        ];
    }
    
}

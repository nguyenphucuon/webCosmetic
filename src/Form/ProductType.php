<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\Category;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\File;


class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Category', EntityType::class,array('class'=>'App\Entity\Category','choice_label'=>"Name"))
            ->add('productName',TextType::class)
            ->add('productPrice',TextType::class)
            ->add('productDescription',TextareaType::class)
            ->add('productDate', DateType::class)
            ->add('Image',FileType::class, [
                'mapped'=> false,
                'required'=> false,
                'constraints'=>[

                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}

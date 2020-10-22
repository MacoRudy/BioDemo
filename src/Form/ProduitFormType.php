<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Producteur;
use App\Entity\Produit;
use Doctrine\DBAL\Types\FloatType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProduitFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom : ',
                'label_attr' => [
                    'class' => 'col-sm-12 col-lg-4 col-form-label'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('prix', NumberType::class, [
                'label' => 'Prix : ',
                'label_attr' => [
                    'class' => 'col-sm-12 col-lg-4 col-form-label'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description : ',
                'label_attr' => [
                    'class' => 'col-sm-12 col-lg-4 col-form-label'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('producteur', EntityType::class, [
                'class' => Producteur::class,
                'label' => 'Producteur : ',
                'choice_label' => 'nom',
                'label_attr' => [
                    'class' => 'col-sm-12 col-lg-4 col-form-label'
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
            ])
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'placeholder' => 'Votre Choix',
                'label' => 'Catégorie : ',
                'choice_label' => 'nom',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->where('c.catParent is not null')
                        ->orderBy('c.nom', 'ASC');
                },
                'label_attr' => [
                    'class' => 'col-sm-12 col-lg-4 col-form-label'
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}

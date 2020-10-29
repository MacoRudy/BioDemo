<?php

namespace App\Form;

use App\Entity\Commande;
use App\Entity\Detail;
use App\Entity\Producteur;
use App\Entity\Produit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DetailFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('quantite')
//            ->add('prix')
            ->add('produit', EntityType::class, [
                'class' => Produit::class,
                'label' => 'Produit : ',
                'choice_label' => 'nom',
                'label_attr' => [
                    'class' => 'col-sm-12 col-lg-4 col-form-label'
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
            ])
            ->add('commande', EntityType::class, [
                'class' => Commande::class,
                'label' => 'Commande : ',
                'choice_label' => 'id',
                'label_attr' => [
                    'class' => 'col-sm-12 col-lg-4 col-form-label'
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
            ])
//            ->add('producteur', EntityType::class, [
//                'class' => Producteur::class,
//                'label' => 'Producteur : ',
//                'choice_label' => 'nom',
//                'label_attr' => [
//                    'class' => 'col-sm-12 col-lg-4 col-form-label'
//                ],
//                'attr' => [
//                    'class' => 'form-control'
//                ],
//            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Detail::class,
        ]);
    }
}

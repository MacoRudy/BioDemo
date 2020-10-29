<?php

namespace App\Form;

use App\Entity\Commande;
use App\Entity\Depot;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommandeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('dateCreation', DateTimeType::class,[
            'label' => 'Date de création ',
            "widget" => 'single_text',
            'attr' => ['placeholder' => ""
            ]
        ])
            ->add('dateLivraison', DateTimeType::class,[
                'label' => 'Date de livraison ',
                "widget" => 'single_text',
                'attr' => ['placeholder' => ""
                ]
            ])
            ->add('montant')
            ->add('user', EntityType::class, [
                'class' => User::class,
                'label' => 'Client : ',
                'choice_label' => 'nom',
                'label_attr' => [
                    'class' => 'col-sm-12 col-lg-4 col-form-label'
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
            ])
            ->add('depot', EntityType::class, [
                'class' => Depot::class,
                'label' => 'Dépot : ',
                'choice_label' => 'nom',
                'label_attr' => [
                    'class' => 'col-sm-12 col-lg-4 col-form-label'
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
        ]);
    }
}

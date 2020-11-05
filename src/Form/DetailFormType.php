<?php

namespace App\Form;

use App\Entity\Detail;
use App\Entity\Produit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DetailFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('quantite', IntegerType::class, [
                'label' => 'Quantité : ',
                'attr' => [
                    'min' => 1
                ]
            ])
            ->add('produit', EntityType::class, [
                'class' => Produit::class,
                'label' => 'Produit : ',
                'choice_label' => 'nom',
                'placeholder' => 'Choisissez le produit'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Detail::class,
        ]);
    }
}

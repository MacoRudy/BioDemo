<?php

namespace App\Form;

use App\Entity\Depot;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
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
            ->add('prenom', TextType::class, [
                'label' => 'Prénom : ',
                'label_attr' => [
                    'class' => 'col-sm-12 col-lg-4 col-form-label'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('adresse', TextType::class, [
                'label' => 'Adresse : ',
                'label_attr' => [
                    'class' => 'col-sm-12 col-lg-4 col-form-label'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('codePostal', IntegerType::class, [
                'label' => 'Code Postal : ',
                'label_attr' => [
                    'class' => 'col-sm-12 col-lg-4 col-form-label'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('ville', TextType::class, [
                'label' => 'Ville : ',
                'label_attr' => [
                    'class' => 'col-sm-12 col-lg-4 col-form-label'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Téléphone : ',
                'label_attr' => [
                    'class' => 'col-sm-12 col-lg-4 col-form-label'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email : ',
                'label_attr' => [
                    'class' => 'col-sm-12 col-lg-4 col-form-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                ]
            ])
            ->add('valide', CheckboxType::class, [
                'label' => 'Validé : ',
                'label_attr' => [
                    'class' => 'col-sm-12 col-lg-4 col-form-label'
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
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'first_options' => ['label' => 'Mot de passe : ',
                    'label_attr' => ['class' => 'col-sm-12 col-lg-4 col-form-label'],
                    'attr' => ['class' => 'form-control']
                ],
                'second_options' => ['label' => 'Confirmation : ',
                    'label_attr' => ['class' => 'col-sm-12 col-lg-4 col-form-label'],
                    'attr' => ['class' => 'form-control'],
                ],
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ]);
//            ->add('roles', ChoiceType::class, [
//                'label' => 'Role : ',
//                'label_attr' => [
//                    'class' => 'col-sm-12 col-lg-4 col-form-label'
//                ],
//                'attr' => [
//                    'class' => 'form-control'],
//                'choices' => [
//                    'ROLE_ADMIN' => 'ROLE_ADMIN',
//                    'ROLE_USER' => 'ROLE_USER',
//                    'ROLE_PRODUCTEUR' => 'ROLE_PRODUCTEUR',
//                ],
//                'multiple' => true,
//            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Karser\Recaptcha3Bundle\Form\Recaptcha3Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;

class AdminRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'attr' => [
                    'class' => 'form-control mb-3',
                    'placeholder' => 'Jean-Christopher Lourd',
                    'minlength' => '4',
                    'maxlenght' => '50',
                ],
                'label' => 'Votre nom complet',
                'label_attr' => [
                    'class' => 'form-label mt-3'
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 4, 'max' => 50])
                ]
            ])
            ->add('pseudo', TextType::class, [
                'attr' => [
                    'class' => 'form-control mb-3',
                    'placeholder' => 'sul243',
                    'minlength' => '4',
                    'maxlenght' => '50',
                ],
                'required' => false,
                'label' => 'Votre Pseudo',
                'label_attr' => [
                    'class' => 'form-label'
                ]
            ])
            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => 'form-control mb-3',
                    'placeholder' => 'exemple@serveur.com',
                    'minlength' => '4',
                    'maxlenght' => '50',
                ],
                'label' => 'Votre adresse mail',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Email(),
                    new Assert\Length(['min' => 4, 'max' => 50])
                ]
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'Mot de passe',
                    'attr' => [
                        'class' => 'form-control mb-3',
                        'placeholder' => '*****',
                    ]
                ],
                'second_options' => [
                    'label' => 'Confirmez le passe',
                    'attr' => [
                        'class' => 'form-control',
                        'placeholder' => '*****',
                    ]
                ],
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'CREER MON COMPTE ADMINISTRATEUR',
                'attr' => [
                    'class' => 'btn btn-success mt-3'
                ]
            ])
            ->add('captcha', Recaptcha3Type::class, [
                'constraints' => new Recaptcha3(),
                'action_name' => 'inscription'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}

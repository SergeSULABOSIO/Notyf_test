<?php

namespace App\Form;

use App\Entity\Entreprise;
use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use App\Controller\Admin\ClientCrudController;
use Karser\Recaptcha3Bundle\Form\Recaptcha3Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;

class EntrepriseRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'ABCD Insurance Brokers',
                    'minlength' => '4',
                    'maxlenght' => '50',
                ],
                'label' => "Le nom complet de votre entreprise (Raison Sociale)",
                'label_attr' => [
                    'class' => 'form-label mt-3'
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 4, 'max' => 50])
                ]
            ])
            ->add('adresse', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => "L'adresse",
                    'minlength' => '4',
                    'maxlenght' => '50',
                ],
                'label' => "Votre adresse physique",
                'label_attr' => [
                    'class' => 'form-label mt-3'
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 4, 'max' => 50])
                ]
            ])
            ->add('telephone', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => "+244828727706, +244828727707, etc.",
                    'minlength' => '4',
                    'maxlenght' => '100',
                ],
                'label' => "Numéros de téléphone",
                'label_attr' => [
                    'class' => 'form-label mt-3'
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 4, 'max' => 50])
                ]
            ])
            ->add('rccm', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => "Exemple: CK/BEN/RCCM/16-B-10580",
                    'minlength' => '4',
                    'maxlenght' => '100',
                ],
                'label' => "Numéros de registre commercial (RCCM) ou l'équivalent.",
                'label_attr' => [
                    'class' => 'form-label mt-3'
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 4, 'max' => 50])
                ]
            ])
            ->add('idnat', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => "Exemple: 01–62–N15130B",
                    'minlength' => '4',
                    'maxlenght' => '100',
                ],
                'label' => "Numéros d'identification nationle (IDNAT) ou l'équivalent.",
                'label_attr' => [
                    'class' => 'form-label mt-3'
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 4, 'max' => 50])
                ]
            ])
            ->add('numimpot', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => "Exemple: A1621859CF",
                    'minlength' => '4',
                    'maxlenght' => '100',
                ],
                'label' => "Numéros d'identification financière (NIF) ou l'équivalent.",
                'label_attr' => [
                    'class' => 'form-label mt-3'
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 4, 'max' => 50])
                ]
            ])
            ->add('secteur', ChoiceType::class, [
                'choices' => ClientCrudController::TAB_CLIENT_SECTEUR,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => "Séléctionnez ici"
                ],
                'label' => "Votre secteur d'Activité.",
                'label_attr' => [
                    'class' => 'form-label mt-3'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'CREER MON ENTREPRISE',
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
            'data_class' => Entreprise::class,
        ]);
    }
}

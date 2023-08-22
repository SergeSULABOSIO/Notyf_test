<?php

namespace App\Form;

use App\Entity\ElementFacture;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ElementFactureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('montant')
            ->add('updatedAt')
            ->add('createdAt')
            ->add('police')
            ->add('entreprise')
            ->add('utilisateur')
            ->add('facture')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ElementFacture::class,
        ]);
    }
}

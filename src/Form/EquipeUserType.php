<?php

namespace App\Form;

use App\Entity\EquipeUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipeUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateDebut', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date de début',
                'attr' => ['class' => 'form-control']
            ])
            ->add('dateFin', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date de fin',
                'attr' => ['class' => 'form-control']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EquipeUser::class,
        ]);
    }
}

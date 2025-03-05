<?php

namespace App\Form;

use App\Entity\Equipe;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomEquipe', null, [
                'label' => 'Nom de l\'équipe',
                'attr' => ['class' => 'form-control']
            ])
            ->add('dateDebut', DateTimeType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'label' => 'Date de début'
            ])
            ->add('dateFin', DateTimeType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'label' => 'Date de fin'
            ])
            
            ->add('chefEquipe', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getNom() . ' ' . $user->getPrenom();
                },
                'required' => false,
                'label' => 'Chef d\'équipe',
                'placeholder' => 'Sélectionner un chef d\'équipe', // Optionnel
                'attr' => ['class' => 'form-select']
            ])
            ->add('membres', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getNom() . ' ' . $user->getPrenom();
                },
                'label' => 'Membres de l\'équipe',
                'multiple' => true,
                'expanded' => true, // ✅ Affichage en checkbox
                'mapped' => false, // Géré manuellement dans le controller
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => ['class' => 'btn btn-success']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Equipe::class,
        ]);
    }
}

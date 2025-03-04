<?php

namespace App\Form;

use App\Entity\Equipe;
use App\Entity\EquipeUser;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipeUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateDebut', null, [
                'widget' => 'single_text',
            ])
            ->add('dateFin', null, [
                'widget' => 'single_text',
            ])
            ->add('utilisateur', EntityType::class, [
                'class' => User::class,  // Assurez-vous d'utiliser la bonne entité
                'choice_label' => 'nom',  // Choisissez un champ approprié pour afficher
                'multiple' => false,  // Ici, assurez-vous que ce champ est pour un seul utilisateur
                'expanded' => false,  // Si vous utilisez des cases à cocher, mettez `true` pour permettre des sélections multiples
            ])
            ->add('equipe', EntityType::class, [
                'class' => Equipe::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EquipeUser::class,
        ]);
    }
}

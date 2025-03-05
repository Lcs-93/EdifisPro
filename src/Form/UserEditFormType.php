<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserEditFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Votre nom'],
                'constraints' => [
                    new NotBlank(['message' => 'Le nom ne peut pas être vide']),
                ],
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Votre prénom'],
                'constraints' => [
                    new NotBlank(['message' => 'Le prénom ne peut pas être vide']),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Votre email'],
                'constraints' => [
                    new NotBlank(['message' => 'L’email ne peut pas être vide']),
                    new Email(['message' => 'Veuillez entrer un email valide']),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Nouveau mot de passe',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Laisser vide si inchangé'],
                'constraints' => [
                    new Length([
                        'min' => 12,
                        'minMessage' => 'Le mot de passe doit contenir au moins 12 caractères',
                    ]),
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Mettre à jour',
                'attr' => ['class' => 'btn btn-primary w-100'],
            ]);
    }
}

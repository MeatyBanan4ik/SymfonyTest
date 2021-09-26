<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Пожалуйста, введите email'
                ],
                'constraints' => [
                    new Email(),
                    new NotBlank(),
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'mapped' => false,
                'type' => PasswordType::class,
                'invalid_message' => 'Пароли должны совпадать',
                'options' => [
                    'attr' => [
                        'class' => 'form-control'
                    ],
                ],
                'first_options' => ['label' => 'Пароль',
                    'attr' => [
                        'autocomplete' => 'new-password',
                        'class' => 'form-control',
                        'placeholder' => 'Пожалуйста, введите пароль'
                    ],
                ],
                'second_options' => ['label' => 'Повторите пароль',
                    'attr' => [
                        'placeholder' => 'Пожалуйста, повторите пароль'
                    ]
                ],
                'constraints' => [
                    new NotBlank([]),
                    new Length([
                        'min' => 6,
                        'max' => 4096,
                        ]),
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

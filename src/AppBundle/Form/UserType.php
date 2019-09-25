<?php

namespace AppBundle\Form;

use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $required = $options['isFromAdmin'];
        $newUser = $options['isNewUser'] ?: false;

        $builder
            ->add('username', TextType::class, [
                'label' => 'form.user.username'
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'form.user.password_mismatch',
                'required' => !$required,
                'first_options'  => ['label' => 'form.user.password'],
                'second_options' => ['label' => 'form.user.password_repeat'],
            ])
            ->add('email', EmailType::class, [
                'label' => 'form.user.email'
            ])
        ;

        if($required && !$newUser) {
            $builder->add('roles', CollectionType::class, [
                'label' => 'form.user.role',
                'entry_type' => ChoiceType::class,
                'entry_options' => [
                    'choices' => [
                        'user.role_admin' => User::ROLE_ADMIN,
                        'user.role_user' => User::ROLE_USER
                    ],
                    'label' => false
                ]
            ]);
        }

        if($required && $newUser) {
            $builder->add('roles', ChoiceType::class, [
                'label' => 'form.user.role',
                'choices' => [
                    'user.role_admin' => User::ROLE_ADMIN,
                    'user.role_user' => User::ROLE_USER
                ]
            ]);
        }

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['isFromAdmin', 'isNewUser']);
    }
}

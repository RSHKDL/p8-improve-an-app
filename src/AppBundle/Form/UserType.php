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
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isFromAdmin = $options['isFromAdmin'];
        $isNewUser = $options['isNewUser'];
        $editSelf = $options['editSelf'];

        $builder
            ->add('username', TextType::class, [
                'label' => 'form.user.username'
            ])
            ->add('email', EmailType::class, [
                'label' => 'form.user.email'
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'user.password.mismatch',
                'mapped' => false,
                'required' => true,
                'first_options'  => ['label' => 'form.user.password'],
                'second_options' => ['label' => 'form.user.password_repeat'],
                'constraints'=> [
                    new NotBlank(['message' => 'user.password.mandatory'])
                ]
            ])
        ;

        if($isFromAdmin && !$isNewUser) {
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

        //@todo admin should not change user password. Add a reset forgotten password service instead.
        /*if($isFromAdmin && !$editSelf) {
            $builder->remove('password');
        }*/

        if($isFromAdmin && $isNewUser) {
            $builder->add('roles', ChoiceType::class, [
                'label' => 'form.user.role',
                'choices' => [
                    'user.role_admin' => User::ROLE_ADMIN,
                    'user.role_user' => User::ROLE_USER
                ]
            ]);
        }

        /*->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'form.user.password_mismatch',
                'required' => !$isFromAdmin,
                'first_options'  => ['label' => 'form.user.password'],
                'second_options' => ['label' => 'form.user.password_repeat'],
            ])*/

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class
        ]);
        $resolver->setRequired(['isFromAdmin', 'isNewUser', 'editSelf']);
    }
}

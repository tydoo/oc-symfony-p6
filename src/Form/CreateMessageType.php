<?php

namespace App\Form;

use App\Entity\Message;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CreateMessageType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options): void {
        $builder
            ->add('message', TextType::class, [
                'attr' => ['class' => 'w-full', 'placeholder' => 'Votre message'],
                'row_attr' => ['class' => 'w-full md:w-1/2'],
                'label_attr' => ['class' => 'hidden'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir un message']),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Le message doit contenir au moins {{ limit }} caractères',
                        'max' => 255,
                        'maxMessage' => 'Le message doit contenir au maximum {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Poster le message',
                'attr' => ['class' => 'btn btn-primary'],
                'row_attr' => ['class' => 'm-0'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void {
        $resolver->setDefaults([
            'data_class' => Message::class,
        ]);
    }
}

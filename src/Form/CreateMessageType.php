<?php

namespace App\Form;

use App\Entity\Message;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CreateMessageType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options): void {
        $builder
            ->add('message', TextType::class, [
                'attr' => ['class' => 'w-full', 'placeholder' => 'Votre message'],
                'row_attr' => ['class' => 'w-full md:w-1/2'],
                'label_attr' => ['class' => 'hidden'],
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

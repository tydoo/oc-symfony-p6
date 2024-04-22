<?php

namespace App\Form;

use App\Entity\Video;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class VideoType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options): void {
        $builder
            ->add('path', TextareaType::class, [
                'label' => 'Code d\'intégration de la vidéo',
                'attr' => [
                    'class' => 'w-full !py-4 !w-[350px]',
                    'placeholder' => 'Rentrez le code d\'intégration de la vidéo',
                    'rows' => '4',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez renseigner le code d\'intégration de la vidéo',
                    ]),
                    new Regex([
                        'pattern' => '/(youtube|dailymotion)/',
                        'message' => 'Le code d\'intégration de la vidéo doit contenir "youtube" ou "dailymotion"',
                    ]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer la vidéo',
                'attr' => ['class' => 'btn btn-primary'],
                'row_attr' => ['class' => 'mb-0 flex justify-center mt-4'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void {
        $resolver->setDefaults([
            'attr' => [
                'id' => 'video-form'
            ],
            'data_class' => Video::class,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\Photo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class FeaturedPhotoType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options): void {
        $builder
            ->add('photo', FileType::class, [
                'label' => "<i class='fa-solid fa-pencil'></i>",
                'label_html' => true,
                'label_attr' => [
                    'class' => 'btn mb-0',
                ],
                'row_attr' => [
                    'class' => 'mb-0',
                ],
                'attr' => [
                    'accept' => 'image/jpeg, image/png, image/gif, image/webp',
                    'class' => '!hidden',
                    'onchange' => 'document.getElementById("featured-photo-form").submit();',
                ],
                'constraints' => [
                    new Image([
                        'maxSize' => '5M',
                        'maxSizeMessage' => 'Le fichier est trop lourd ({{ size }} {{ suffix }}). La taille maximale autorisée est de {{ limit }} {{ suffix }}.',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
                        'mimeTypesMessage' => 'Le fichier n\'est pas une image valide. Les formats autorisés sont : {{ types }}.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void {
        $resolver->setDefaults([
            'attr' => [
                'id' => 'featured-photo-form',
            ],
        ]);
    }
}

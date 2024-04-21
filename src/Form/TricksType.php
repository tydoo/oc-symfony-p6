<?php

namespace App\Form;

use App\Entity\Figure;
use App\Entity\Category;
use App\Entity\Photo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class TricksType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options): void {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de la figure',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir un nom pour la figure',
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Le nom de la figure doit contenir au moins {{ limit }} caractères',
                        'max' => 255,
                        'maxMessage' => 'Le nom de la figure doit contenir au maximum {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description de la figure',
                'attr' => ['rows' => 5],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir une description pour la figure',
                    ]),
                    new Length([
                        'min' => 10,
                        'max' => 255,
                        'minMessage' => 'La description de la figure doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'La description de la figure doit contenir au maximum {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('category', EntityType::class, [
                'label' => 'Catégorie de la figure',
                'class' => Category::class,
                'choice_label' => 'name',
                'autocomplete' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez choisir une catégorie pour la figure',
                    ]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer la figure',
                'attr' => ['class' => 'btn btn-primary'],
                'row_attr' => ['class' => 'mb-0'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void {
        $resolver->setDefaults([
            'data_class' => Figure::class,
        ]);
    }
}

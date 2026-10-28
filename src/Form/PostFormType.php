<?php

namespace App\Form;

use App\Entity\Post;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;   

class PostFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, [
                'required' => true,
                'attr' =>['class' => 'form-control'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'El titulo es obligatorio',
                    ])
                ]
            ])
            ->add('content', null, [
                'required' => true,
                'attr' =>['class' => 'form-control'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'El contenido es obligatorio',
                    ])
                ]
            ])
            ->add('image', FileType::class, array(
                'label' => 'Image',
                'mapped' => false,
                'required' => true,
                'attr' =>['class' => 'form-control'],
                'constraints' => [
                    new File([
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image',
                    ]) 
                ]
            ))
            ->add('Send', SubmitType::class,
                ['attr' =>['class' => 'btn btn-primary']]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}

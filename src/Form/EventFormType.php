<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Salon;
use DateTime;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;




class EventFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'format' => DateType::HTML5_FORMAT,
                'constraints' => [new Assert\NotBlank(['message' => 'La date ne peut pas être vide'])]
            ])
            ->add('heure', TimeType::class, [
                'widget' => 'single_text',
                'html5' => false,
                'attr' => ['class' => 'js-timepicker'],
                'input' => 'datetime',
                'constraints' => [new Assert\NotBlank(['message' => 'L\' heure ne peut pas être vide'])]
            ])
            ->add('message', TextType::class, [
                'constraints' => [new Assert\NotBlank(['message' => 'Le message ne peut pas être vide'])]
            ])
            ->add('salon', EntityType::class, [
                'class' => Salon::class,
                'constraints' => [new Assert\NotBlank(['message' => 'Le salon ne peut pas être vide'])]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\Pregunta;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PreguntaFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('texto', TextType::class, [
                'label' => 'Pregunta',
            ])
            ->add('respuesta1', TextType::class, [
                'label' => 'Respuesta 1',
                'required' => true,
            ])
            ->add('respuesta2', TextType::class, [
                'label' => 'Respuesta 2',
                'required' => true,
            ])
            ->add('respuesta3', TextType::class, [
                'label' => 'Respuesta 3',
                'required' => false,
            ])
            ->add('respuesta4', TextType::class, [
                'label' => 'Respuesta 4',
                'required' => false,
            ])
            ->add('fechaInicio', DateTimeType::class, [
                'label' => 'Fecha de inicio',
                'widget' => 'single_text',
            ])
            ->add('fechaFin', DateTimeType::class, [
                'label' => 'Fecha de fin',
                'widget' => 'single_text',
            ])
            ->add('solucion', TextType::class, [
                'label' => 'Número de solución (1-4)',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Pregunta::class,
        ]);
    }
}

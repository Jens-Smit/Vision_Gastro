<?php

namespace App\Form;

use App\Entity\Company;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompanyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => ['placeholder' => 'Bezeichung des Unternehmens'],
            ])
            ->add('sign', TextType::class, [
                'label' => 'Unternehmenskürzel',
                'attr' => [
                    'placeholder' => 'Unternehmenskürzel (maximal 5 Zeichen)', 
                    'maxlength' => 5
                ],
            ])
            ->add('onjekt_admin')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Company::class,
        ]);
    }
}

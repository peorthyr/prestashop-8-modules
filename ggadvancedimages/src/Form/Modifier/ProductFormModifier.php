<?php

declare(strict_types=1);

namespace PrestaShop\Module\Ggadvancedimages\Form\Modifier;

use Language;
use PrestaShopBundle\Form\FormBuilderModifier;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;

class ProductFormModifier
{
    public function __construct(private readonly FormBuilderModifier $modifier)
    {
    }

    public function modify(FormBuilderInterface $builder): void
    {
        $choices = [];
        foreach (Language::getLanguages(false) as $lang) {
            $choices[$lang['name']] = $lang['id_lang'];
        }

        $this->modifier->addAfter(
            $builder,
            'basic',
            'gg_image_file',
            FileType::class,
            [
                'required' => false,
                'label' => 'Advanced image',
            ]
        );

        $this->modifier->addAfter(
            $builder,
            'gg_image_file',
            'gg_image_lang',
            ChoiceType::class,
            [
                'label' => 'Language',
                'choices' => $choices,
                'expanded' => true,
                'multiple' => false,
            ]
        );

        $this->modifier->addAfter(
            $builder,
            'gg_image_lang',
            'gg_image_user',
            ChoiceType::class,
            [
                'label' => 'User type',
                'choices' => [
                    'Guests' => 1,
                    'Logged in' => 0,
                ],
                'expanded' => true,
            ]
        );
    }
}

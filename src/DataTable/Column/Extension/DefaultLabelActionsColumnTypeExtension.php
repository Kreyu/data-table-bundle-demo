<?php

declare(strict_types=1);

namespace App\DataTable\Column\Extension;

use Kreyu\Bundle\DataTableBundle\Column\Extension\AbstractColumnTypeExtension;
use Kreyu\Bundle\DataTableBundle\Column\Type\ActionsColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DefaultLabelActionsColumnTypeExtension extends AbstractColumnTypeExtension
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('label', 'label.actions');
    }

    public static function getExtendedTypes(): iterable
    {
        return [
            ActionsColumnType::class,
        ];
    }
}

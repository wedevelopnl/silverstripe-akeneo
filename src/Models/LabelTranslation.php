<?php

namespace WeDevelop\Akeneo\Models;

use SilverStripe\ORM\DataObject;

/**
 * @property string $Label
 */
class LabelTranslation extends DataObject
{
    /** @config */
    private static string $table_name = 'Akeneo_Label_Translations';

    /** @config */
    private static array $db = [
        'Label' => 'Varchar',
    ];

    /** @config */
    private static array $has_one = [
        'Locale' => Locale::class,
        'Family' => Family::class,
        'FamilyVariant' => FamilyVariant::class,
        'ProductAttribute' => ProductAttribute::class,
        'ProductAttributeGroup' => ProductAttributeGroup::class,
        'ProductAttributeOption' => ProductAttributeOption::class,
        'ProductAssociation' => ProductAssociation::class,
        'ProductCategory' => ProductCategory::class,
        'ProductModel' => ProductModel::class,
        'ProductMediaFile' => ProductMediaFile::class,
        'ProductImage' => ProductImage::class
    ];
}

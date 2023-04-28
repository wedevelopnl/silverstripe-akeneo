<?php

namespace WeDevelop\Akeneo\Admins;

use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\TabSet;
use SilverStripe\ORM\DataObject;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;
use WeDevelop\Akeneo\Imports\AkeneoImport;
use WeDevelop\Akeneo\Models\Display\DisplayGroup;
use WeDevelop\Akeneo\Models\Family;
use WeDevelop\Akeneo\Models\Product;
use WeDevelop\Akeneo\Models\ProductAttribute;
use WeDevelop\Akeneo\Models\ProductCategory;
use WeDevelop\Akeneo\Models\ProductModel;
use WeDevelop\Config\AkeneoConfig;

class AkeneoAdmin extends ModelAdmin
{
    /** @config */
    private static array $managed_models = [
        Product::class,
        ProductModel::class,
        ProductCategory::class,
        ProductAttribute::class,
        Family::class,
    ];

    /** @config */
    private static string $url_segment = 'akeneo';

    /** @config */
    private static string $menu_title = 'Akeneo';

    /** @config */
    private static string $menu_icon = 'wedevelopnl/silverstripe-akeneo:images/akeneo.png';

    public function getEditForm($id = null, $fields = null)
    {
        $form = parent::getEditForm($id, $fields);

        if ($this->modelClass === ProductCategory::class && $gridField = $form->Fields()->dataFieldByName($this->sanitiseClassName($this->modelClass))) {
            if ($gridField instanceof GridField) {
                $gridField->getConfig()->addComponent(new GridFieldOrderableRows('Sort'));
            }
        }

        if ($this->modelClass === DisplayGroup::class && $gridField = $form->Fields()->dataFieldByName($this->sanitiseClassName($this->modelClass))) {
            if ($gridField instanceof GridField) {
                $originalField = clone $gridField;

                $originalField->setList(
                    $originalField->getList()->filter([
                        'IsRootGroup' => 0,
                    ])
                );

                $gridField->setName('RootDisplayGroups');
                $gridField->setList(
                    $gridField->getList()->filter([
                        'IsRootGroup' => 1,
                    ])
                );
            }

            $fields = $form->Fields();

            $fields->push(TabSet::create('Root', 'Root'));
            $fields->addFieldsToTab('Root.Root Groups', [
                HeaderField::create('RootHeader', 'Root groups have no parent group associated with them'),
                $gridField,
            ]);

            $fields->addFieldsToTab('Root.Sub Groups', [
                HeaderField::create('SubHeader', 'Sub groups are part of a group chain, and therefore have a parent group defined somewhere.'),
                $originalField,
            ]);
        }

        $form->Actions()->push(
            FormAction::create('doSync', 'Sync with Akeneo')
                ->setUseButtonTag(true)
                ->addExtraClass('btn btn-primary mt-2 mb-2 icon font-icon-sync')
        );

        return $form;
    }

    public function doSync(): void
    {
        /** @var  AkeneoImport $import */
        $import = Injector::inst()->get('AkeneoImport');
        $import->setVerbose(false);
        $import->run([]);

        Controller::curr()->getResponse()->addHeader('X-Status', 'Synced');
    }

    public function getCMSEditLink(DataObject $object, string $subTab = ''): string
    {
        $sanitisedClassname = $this->sanitiseClassName($object::class);

        $editFormField = 'EditForm/field/';

        if ($subTab) {
            $editFormField .= $subTab . '/';
        }

        return Controller::join_links(
            $this->Link($sanitisedClassname),
            $editFormField,
            $sanitisedClassname,
            'item',
            $object->ID
        );
    }

    public function getManagedModels(): array
    {
        $managed = parent::getManagedModels();

        if (AkeneoConfig::getEnableDisplayGroups()) {
            $managed[DisplayGroup::class] = [
                'title' => DisplayGroup::singleton()->plural_name(),
                'dataClass' => DisplayGroup::class,
            ];
        }

        return $managed;
    }
}

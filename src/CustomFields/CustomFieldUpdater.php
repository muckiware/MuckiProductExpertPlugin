<?php declare(strict_types=1);
/**
 * MuckiProductExpertPlugin
 *
 * @category   SW6 Plugin
 * @package    MuckiProductExpert
 * @copyright  Copyright (c) 2025 by Muckiware
 * @license    MIT
 * @author     Muckiware
 *
 */
namespace MuckiProductExpertPlugin\CustomFields;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;

class CustomFieldUpdater
{
    private string $customFieldPrefix = '_custom_field__';
    private string $customFieldsetPrefix = '_custom_field_set__';
    private string $customFieldConfigPath;

    public function __construct(
        protected EntityRepository $customFieldSetRepository,
        protected EntityRepository $customFieldRepository,
        protected string $pluginPath,
        protected string $pluginName
    ) {
        $this->customFieldConfigPath = $this->pluginPath.'/CustomFields/CustomFieldSets/'.$this->pluginName.'.json';
    }

    public function sync(): void
    {
        $this->deleteRemoved();

        foreach ($this->getCustomFieldSetStorage() as $customFieldSetData) {
            $customFieldSet = $this->getCustomFieldSet($customFieldSetData['name']);

            if (!$customFieldSet) {
                $this->createCustomFieldset($customFieldSetData);
            }

            foreach ($customFieldSetData['customFields'] as $customFieldData) {
                if (!$this->getCustomField($customFieldSetData['name'], $customFieldData['name'])) {
                    $this->createCustomField($customFieldSetData['name'], $customFieldData);
                }
            }
        }
    }

    private function getCustomFieldSetStorage(): array
    {
        $customFieldSetStorage = [];

        $customFieldSetStorageFiles = glob($this->customFieldConfigPath);

        if ($customFieldSetStorageFiles) {
            foreach ($customFieldSetStorageFiles as $customFieldSetFile) {
                $customFieldSetStorage[] = json_decode(file_get_contents($customFieldSetFile), true);
            }
        }

        return $customFieldSetStorage;
    }

    private function createCustomFieldset($customFieldSet): void
    {
        if (!$customFieldSet['name'] || !$customFieldSet['config'] || !$customFieldSet['relations']) {
            return;
        }

        $this->customFieldSetRepository->create([
            [
                'id' => Uuid::randomHex(),
                'name' => $customFieldSet['name'],
                'config' => $customFieldSet['config'],
                'relations' => $customFieldSet['relations'],
            ],
        ], Context::createDefaultContext());
    }

    private function createCustomField($setName, $customField): void
    {
        if (!$customField['name'] || !$customField['config']) {
            return;
        }

        $customFieldSet = $this->getCustomFieldSet($setName);

        if (!$customFieldSet) {
            return;
        }

        $this->customFieldRepository->create([
            [
                'id' => Uuid::randomHex(),
                'name' => $customField['name'],
                'type' => $customField['type'],
                'config' => $customField['config'],
                'customFieldSetId' => $customFieldSet->getId(),
            ],
        ], Context::createDefaultContext());
    }

    private function getCustomField($setName, $fieldName): ?object
    {
        if (!$setName || !$fieldName) {
            return null;
        }

        $customFieldSet = $this->getCustomFieldSet($setName);

        if (!$customFieldSet) {
            return null;
        }

        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('name', $fieldName))
            ->addFilter(new EqualsFilter('customFieldSetId', $customFieldSet->getId()));

        return $this->customFieldRepository
            ->search($criteria, Context::createDefaultContext())
            ->first();
    }

    private function getCustomFieldSet($name): ?object
    {
        if (!$name) {
            return null;
        }

        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('name', $name));

        $result = $this->customFieldSetRepository->search(
            $criteria, Context::createDefaultContext()
        );

        return $result->first();
    }

    public function remove(): void
    {
        // delete custom fields
        $criteria = (new Criteria())
            ->addFilter(new ContainsFilter('name', $this->customFieldPrefix));

        $customFields = $this->customFieldRepository->search($criteria, Context::createDefaultContext());

        foreach ($customFields as $customField) {
            $this->removeCustomField($customField->getId());
        }

        // delete custom field sets
        $criteria = (new Criteria())
            ->addFilter(new ContainsFilter('name', $this->customFieldsetPrefix));

        $customFieldSets = $this->customFieldSetRepository->search($criteria, Context::createDefaultContext());

        foreach ($customFieldSets as $customFieldSet) {
            $this->removeCustomFieldset($customFieldSet->getId());
        }
    }

    private function removeCustomField($id): void
    {
        $this->customFieldRepository->delete([['id' => $id]], Context::createDefaultContext());
    }

    private function removeCustomFieldset($id): void
    {
        $this->customFieldSetRepository->delete([['id' => $id]], Context::createDefaultContext());
    }

    private function deleteRemoved(): void
    {
        $customFieldSetStorage['field_sets'] = [];
        $customFieldSetStorage['fields'] = [];

        foreach ($this->getCustomFieldSetStorage() as $customFieldSetData) {
            $customFieldSetStorage['field_sets'][] = $customFieldSetData['name'];

            $customFieldSetStorage['fields'] = array_merge(
                $customFieldSetStorage['fields'],
                $customFieldSetData['customFields']
            );
        }

        $criteria = (new Criteria())
            ->addFilter(new ContainsFilter('name', $this->customFieldPrefix));

        $customFields = $this->customFieldRepository->search($criteria, Context::createDefaultContext());

        foreach ($customFields as $customField) {
            if (in_array($customField->getName(), $customFieldSetStorage['fields'])) {
                continue;
            }
            $this->removeCustomField($customField->getId());
        };

        $criteria = (new Criteria())
            ->addFilter(new ContainsFilter('name', $this->customFieldsetPrefix));

        $customFieldSets = $this->customFieldSetRepository->search($criteria, Context::createDefaultContext());

        foreach ($customFieldSets as $customFieldSet) {
            if (in_array($customFieldSet->getName(), $customFieldSetStorage['field_sets'])) {
                continue;
            }
            $this->removeCustomFieldset($customFieldSet->getId());
        }
    }
}

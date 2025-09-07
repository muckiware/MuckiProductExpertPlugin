const { Module, Feature } = Shopware;

Shopware.Component.extend(
    'sw-custom-field-type-text',
    'sw-custom-field-type-base',
    () => import('./component/sw-custom-field-type-text'),
);


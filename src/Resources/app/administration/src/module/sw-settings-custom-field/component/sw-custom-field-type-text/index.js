
export default {
    data() {
        return {
            propertyNames: {
                label: this.$tc('sw-settings-custom-field.customField.detail.labelLabel'),
                placeholder: this.$tc('sw-settings-custom-field.customField.detail.labelPlaceholder'),
                helpText: this.$tc('sw-settings-custom-field.customField.detail.labelHelpText'),
            },
        };
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {

            console.log('validate customFields input', this.currentCustomField);
            if (!this.currentCustomField.config.hasOwnProperty('dateType')) {
                this.$set(this.currentCustomField.config, 'dateType', 'datetime');
            }

            if (!this.currentCustomField.config.hasOwnProperty('config')) {
                this.$set(this.currentCustomField.config, 'config', {
                    time_24hr: true,
                });
            }
        }
    }
};

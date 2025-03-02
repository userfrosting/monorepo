<script setup lang="ts">
import { useConfigStore } from '@userfrosting/sprinkle-core/stores'
import type { UserCreateRequest, UserEditRequest } from '@userfrosting/sprinkle-admin/interfaces'
import type { GroupInterface } from '@userfrosting/sprinkle-account/interfaces'

/**
 * Props
 */
const { groups } = defineProps<{
    groups: GroupInterface[]
}>()

/**
 * Form Model
 */
const formData = defineModel<UserEditRequest | UserCreateRequest>({
    default: {
        user_name: '',
        group_id: '',
        first_name: '',
        last_name: '',
        email: '',
        locale: ''
    }
})

function getAvailableLocales(): string[] {
    return useConfigStore().get('locales.available')
}

function getDefaultLocale(): string {
    return useConfigStore().get('site.registration.user_defaults.locale', 'en_US')
}

// Apply default locale to form data
formData.value.locale = getDefaultLocale()

/**
 * Emits
 */
const emits = defineEmits(['submit'])
</script>

<template>
    <form v-on:submit.prevent="emits('submit')">
        <fieldset class="uk-fieldset uk-form-stacked">
            <div class="uk-margin">
                <label class="uk-form-label" for="form-stacked-text">{{ $t('USERNAME') }}</label>
                <div class="uk-inline uk-width-1-1">
                    <font-awesome-icon class="fa-form-icon" icon="edit" fixed-width />
                    <input
                        class="uk-input"
                        type="text"
                        :placeholder="$t('USERNAME')"
                        aria-label="Username"
                        data-test="user_name"
                        autofocus
                        tabindex="1"
                        autocomplete="false"
                        v-model="formData.user_name" />
                </div>
            </div>

            <div class="uk-margin">
                <label class="uk-form-label" for="form-stacked-text">{{ $t('GROUP') }}</label>
                <div class="uk-inline uk-width-1-1">
                    <font-awesome-icon class="fa-form-icon" icon="users" fixed-width />
                    <select
                        class="uk-input uk-select"
                        aria-label="Group"
                        data-test="group"
                        tabindex="2"
                        v-model="formData.group_id">
                        <option value="0">None</option>
                        <option disabled="true">-----</option>
                        <option v-for="group in groups" :key="group.id" :value="group.id">
                            {{ group.name }}
                        </option>
                    </select>
                </div>
            </div>

            <div class="uk-margin">
                <label class="uk-form-label" for="form-stacked-text">{{ $t('FIRST_NAME') }}</label>
                <div class="uk-inline uk-width-1-1">
                    <font-awesome-icon class="fa-form-icon" icon="edit" fixed-width />
                    <input
                        class="uk-input"
                        type="text"
                        :placeholder="$t('FIRST_NAME')"
                        aria-label="First Name"
                        data-test="first_name"
                        tabindex="3"
                        v-model="formData.first_name" />
                </div>
            </div>

            <div class="uk-margin">
                <label class="uk-form-label" for="form-stacked-text">{{ $t('LAST_NAME') }}</label>
                <div class="uk-inline uk-width-1-1">
                    <font-awesome-icon class="fa-form-icon" icon="edit" fixed-width />
                    <input
                        class="uk-input"
                        type="text"
                        :placeholder="$t('LAST_NAME')"
                        aria-label="Last Name"
                        data-test="last_name"
                        tabindex="4"
                        v-model="formData.last_name" />
                </div>
            </div>

            <div class="uk-margin">
                <label class="uk-form-label" for="form-stacked-text">{{ $t('EMAIL') }}</label>
                <div class="uk-inline uk-width-1-1">
                    <font-awesome-icon class="fa-form-icon" icon="envelope" fixed-width />
                    <input
                        class="uk-input"
                        type="text"
                        :placeholder="$t('EMAIL')"
                        aria-label="Email"
                        data-test="email"
                        tabindex="5"
                        v-model="formData.email" />
                </div>
            </div>

            <div class="uk-margin">
                <label class="uk-form-label" for="form-stacked-text">{{ $t('LOCALE') }}</label>
                <div class="uk-inline uk-width-1-1">
                    <font-awesome-icon class="fa-form-icon" icon="language" fixed-width />
                    <select
                        class="uk-input uk-select"
                        aria-label="Locale"
                        data-test="locale"
                        tabindex="6"
                        v-model="formData.locale">
                        <option
                            v-for="(value, key) in getAvailableLocales()"
                            :value="key"
                            :key="key">
                            {{ value }}
                        </option>
                    </select>
                </div>
            </div>

            <!--
            {% if 'password' not in form.fields.hidden %}
                {% include "forms/partials/user-set-password.html.twig" %}
            {% endif %}
            -->

            <div class="uk-text-right" uk-margin>
                <button class="uk-button uk-button-default uk-modal-close" type="button">
                    {{ $t('CANCEL') }}
                </button>
                <button class="uk-button uk-button-primary" type="submit" tabindex="7">
                    {{ $t('SAVE') }}
                </button>
            </div>
        </fieldset>
    </form>
</template>

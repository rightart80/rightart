{*
 * PrestaChamps
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Commercial License
 * you can't distribute, modify or sell this code
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file
 * If you need help please contact leo@prestachamps.com
 *
 * @author    Mailchimp
 * @copyright Mailchimp
 * @license   commercial
 *}
 <h2>{l s='Mapping statuses' mod='mailchimppro'}</h2>
{literal}
<div class="form-group">
    <label for="statusForPending">
        {/literal}{l s='Status for pending' mod='mailchimppro'}{literal}
    </label>
    <Multiselect
        v-model="statusForPending"
        id="statusForPending"
        :options="orderStates"
        mode="tags"
        :searchable="true"
    >

        <template v-slot:tag="{ option, handleTagRemove, disabled }">
            <div
                style="background: red !important;"
                :style="{ background: option.color}"
                class="multiselect-tag is-user "
            >
                {{ option.label }}
                <span
                    class="multiselect-tag-remove"
                    @click="handleTagRemove(option, $event)"
                >
                        <span class="multiselect-tag-remove-icon"></span>
                      </span>
            </div>
        </template>
        <template v-slot:option="{ option }">
                <span class="badge badge-pill badge-primary" :style="{ background: option.color}">
                    {{ option.label }}
                </span>
        </template>
    </Multiselect>
</div>
<div class="form-group">
    <label for="statusForRefunded">
        {/literal}{l s='Status for refunded' mod='mailchimppro'}{literal}
    </label>
    <Multiselect
        v-model="statusForRefunded"
        id="statusForRefunded"
        :options="orderStates"
        mode="tags"
        :searchable="true"
    >
        <template v-slot:tag="{ option, handleTagRemove, disabled }">
            <div
                style="background: red !important;"
                :style="{ background: option.color}"
                class="multiselect-tag is-user "
            >
                {{ option.label }}
                <span
                    class="multiselect-tag-remove"
                    @click="handleTagRemove(option, $event)"
                >
                        <span class="multiselect-tag-remove-icon"></span>
                      </span>
            </div>
        </template>
        <template v-slot:option="{ option }">
                <span class="badge badge-pill badge-primary" :style="{ background: option.color}">
                    {{ option.label }}
                </span>
        </template>
    </Multiselect>
</div>
<div class="form-group">
    <label for="statusForCancelled">
        {/literal}{l s='Status for cancelled' mod='mailchimppro'}{literal}
    </label>
    <Multiselect
        v-model="statusForCancelled"
        id="statusForCancelled"
        :options="orderStates"
        mode="tags"
        :searchable="true"
    >
        <template v-slot:tag="{ option, handleTagRemove, disabled }">
            <div
                style="background: red !important;"
                :style="{ background: option.color}"
                class="multiselect-tag is-user "
            >
                {{ option.label }}
                <span
                    class="multiselect-tag-remove"
                    @click="handleTagRemove(option, $event)"
                >
                        <span class="multiselect-tag-remove-icon"></span>
                      </span>
            </div>
        </template>
        <template v-slot:option="{ option }">
                <span class="badge badge-pill badge-primary" :style="{ background: option.color}">
                    {{ option.label }}
                </span>
        </template>
    </Multiselect>
</div>
<div class="form-group">
    <label for="statusForShipped">
        {/literal}{l s='Status for shipped' mod='mailchimppro'}{literal}
    </label>
    <Multiselect
        v-model="statusForShipped"
        id="statusForShipped"
        :options="orderStates"
        mode="tags"
        :searchable="true"
    >
        <template v-slot:tag="{ option, handleTagRemove, disabled }">
            <div
                style="background: red !important;"
                :style="{ background: option.color}"
                class="multiselect-tag is-user "
            >
                {{ option.label }}
                <span
                    class="multiselect-tag-remove"
                    @click="handleTagRemove(option, $event)"
                >
                        <span class="multiselect-tag-remove-icon"></span>
                      </span>
            </div>
        </template>
        <template v-slot:option="{ option }">
                <span class="badge badge-pill badge-primary" :style="{ background: option.color}">
                    {{ option.label }}
                </span>
        </template>
    </Multiselect>
</div>
<div class="form-group">
    <label for="statusForPaid">
        {/literal}{l s='Status for paid' mod='mailchimppro'}{literal}
    </label>
    <Multiselect
        v-model="statusForPaid"
        id="statusForPaid"
        :options="orderStates"
        mode="tags"
        :searchable="true"
    >
        <template v-slot:tag="{ option, handleTagRemove, disabled }">
            <div
                style="background: red !important;"
                :style="{ background: option.color}"
                class="multiselect-tag is-user "
            >
                {{ option.label }}
                <span
                    class="multiselect-tag-remove"
                    @click="handleTagRemove(option, $event)"
                >
                        <span class="multiselect-tag-remove-icon"></span>
                      </span>
            </div>
        </template>
        <template v-slot:option="{ option }">
                <span class="badge badge-pill badge-primary" :style="{ background: option.color}">
                    {{ option.label }}
                </span>
        </template>
    </Multiselect>
</div>{/literal}
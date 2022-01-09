{if $can_create_yandex_draft}
    {if $can_auto_confirm}
        {$but_text = __("yandex_delivery_v3.yandex_delivery_form")}
    {else}
        {$but_text = __("yandex_delivery_v3.create_draft")}
    {/if}
    <div class="clearfix">
        {include file="buttons/button.tpl"
            but_text=$but_text
            but_role="add"
            but_target_id="content_add_new_yandex_order_0"
            but_meta="btn cm-dialog-opener"
        }
    </div>
{/if}
<div id="department_features_{$block.block_id}">
    <div class="ty-feature">
        {if $department_data.main_pair}
            <div class="ty-feature__image">
                {include
                file="common/image.tpl"
                no_ids=true
                images=$department_data.main_pair
                image_width=$settings.Thumbnails.product_lists_thumbnail_width
                image_height=$settings.Thumbnails.product_lists_thumbnail_height
                lazy_load=true}
            </div>
        {/if}
            <div class="ty-feature__description ty-wysiwyg-content">
                {$department_data.description nofilter}
            </div>
    </div>

    {include file="common/pagination.tpl" save_current_page=true save_current_url=true div_id="pagination_contents_departments"}
    {$c_url=$config.current_url|fn_query_remove:"sort_by":"sort_order"}
    {$rev=$smarty.request.content_id|default:"pagination_contents"}
    {include_ext file="common/icon.tpl" class="icon-`$search.sort_order_rev`" assign=c_icon}
    {include_ext file="common/icon.tpl" class="icon-dummy" assign=c_dummy}
        {if $workers}
                <div class="ty-container">
                    <h3>{__("workers")}</h3>
                    
                    <table class="table table-middle table--relative table-responsive">
                        <thead>
                        <tr>
                            <th>
                            <a class="cm-ajax" 
                            href="{"`$c_url`&sort_by=names&sort_order=`$search.sort_order_rev`"|fn_url}" 
                                data-ca-target-id={$rev}>
                                {__("product_reviews.first_and_last_name")}{if $search.sort_by === "name"}
                                {$c_icon nofilter}{else}{$c_dummy nofilter}{/if}
                            </a>
                            </th>
                            <th>
                            <a class="cm-ajax" href="{"`$c_url`&sort_by=email&sort_order=`$search.sort_order_rev`"|fn_url}" 
                                data-ca-target-id={$rev}>{__("email")}{if $search.sort_by === "email"}
                                {$c_icon nofilter}{else}{$c_dummy nofilter}{/if}
                            </a>
                            </th>
                        </tr>
                        </thead>
                        {foreach from=$workers item=worker}
                            <tr>
                                {$allow_save=true}
                                {if $allow_save}
                                    {assign var = "no_hide_input" value ="cm-no-hide-input"}
                                {else}
                                    {assign var = "no_hide_input" value =""}
                                {/if}

                                <td class="ty-categories-picker__title" style="width: 6%" class="{$no_hide_input} ty-left" data-th="{__("name")}">
                                    <p class="row-status" >{$worker.lastname} {$worker.firstname}</p>
                                </td>
                                <td class="ty-categories-picker__title" style="width: 6%" data-th="{__("email")}">
                                    <a class="row-status" href="mailto:{$worker.email|escape:url}">{$worker.email}
                                    </a>
                                </td>
                            </tr>
                        {/foreach}
                    </table>
                </div>
            {else}
                <p class="no-items">{__("no_data")}</p>
            {/if}
        {include file="common/pagination.tpl"}
<!--product_features_{$block.block_id}--></div>
{capture name="mainbox_title"}{$department_data.department nofilter}{/capture}
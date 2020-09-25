{if isset($videoPlayer) && !empty($videoPlayer)}
    <div id="videoPlayer">
        <div class="row">
            {foreach from=$videoPlayer item=video}
                <div class="col-md-4">
                    <div class="video">
                        <iframe class="videoPlayer" src="{$video.url}"frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </div>
                </div>
            {/foreach}
        </div>
    </div>
{/if}
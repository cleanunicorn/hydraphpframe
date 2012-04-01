
    This is the homepage!
{if $logged}Logged{else}Not Logged{/if}

<table border="1" width="960">
    {foreach from=$debug key=k item=v}
    <tr>
        <td>{$k}</td>
        <td>
			{if is_array($v)}
				<table style="width: 100%;">
				{foreach from=$v key=v_k item=v_v}
					<tr>
						<td>
							{$v_k}
						</td>
						<td>
							{$v_v}
						</td>
					</tr>
				{/foreach}
				</table>
			{else}
				{$v}
			{/if}
        </td>
    </tr>
    {/foreach}
</table>

<span style="font-weight: bold;">Geolocation </span><span id="geolocation_identify" style="color: #f00;">Wait ...</span>

<script>
$(document).ready( function()
{
    if ( navigator.geolocation )
    {
        $( '#geolocation_identify' ).text( 'Yes' );
    }
    else
    {
        $( '#geolocation_identify' ).text( 'No' );
    }
} );
</script>
{include file='_header.tpl' title=$page_title keywords=$page_keywords}

{$top_content}
{$page_content}
{$footer_content}

{$footer}

{if isset( $run_time )}
<h1>Run time: {$run_time}</h1>
{/if}

